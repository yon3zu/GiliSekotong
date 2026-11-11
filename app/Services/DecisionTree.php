<?php

namespace App\Services;

/**
 * Implementasi pohon keputusan ID3 / C4.5 untuk atribut kategorikal.
 *
 * - ID3 memilih atribut dengan Information Gain terbesar.
 * - C4.5 memilih atribut dengan Gain Ratio terbesar di antara atribut
 *   yang gain-nya minimal rata-rata (heuristik Quinlan), sehingga tidak
 *   bias ke atribut yang memiliki banyak nilai.
 */
class DecisionTree
{
    public function __construct(
        private string $algorithm = 'C4.5',
        private string $target = 'keputusan',
        private int $minSamples = 2,
        private ?int $maxDepth = null,
    ) {}

    /** Entropy(S) = -Σ p_i log2 p_i */
    public static function entropy(array $rows, string $target): float
    {
        $total = count($rows);
        if ($total === 0) {
            return 0.0;
        }

        $entropy = 0.0;
        foreach (self::distribution($rows, $target) as $count) {
            $p = $count / $total;
            $entropy -= $p * log($p, 2);
        }

        return $entropy;
    }

    public static function distribution(array $rows, string $target): array
    {
        $dist = [];
        foreach ($rows as $row) {
            $dist[$row[$target]] = ($dist[$row[$target]] ?? 0) + 1;
        }
        arsort($dist);

        return $dist;
    }

    public static function partition(array $rows, string $attribute): array
    {
        $parts = [];
        foreach ($rows as $row) {
            $parts[$row[$attribute]][] = $row;
        }
        ksort($parts);

        return $parts;
    }

    /**
     * Hitung Gain, Split Info, dan Gain Ratio satu atribut beserta rincian
     * per nilai atribut (dipakai juga untuk tabel perhitungan manual).
     */
    public static function evaluateAttribute(array $rows, string $attribute, string $target): array
    {
        $total = count($rows);
        $base = self::entropy($rows, $target);
        $weighted = 0.0;
        $splitInfo = 0.0;
        $values = [];

        foreach (self::partition($rows, $attribute) as $value => $subset) {
            $n = count($subset);
            $p = $n / $total;
            $e = self::entropy($subset, $target);
            $weighted += $p * $e;
            $splitInfo -= $p * log($p, 2);
            $values[$value] = [
                'count' => $n,
                'distribution' => self::distribution($subset, $target),
                'entropy' => $e,
            ];
        }

        $gain = $base - $weighted;

        return [
            'attribute' => $attribute,
            'entropy' => $base,
            'gain' => $gain,
            'split_info' => $splitInfo,
            'gain_ratio' => $splitInfo > 0 ? $gain / $splitInfo : 0.0,
            'values' => $values,
        ];
    }

    public function train(array $rows, array $attributes, int $depth = 0): array
    {
        $dist = self::distribution($rows, $this->target);
        $majority = array_key_first($dist);
        $leaf = [
            'type' => 'leaf',
            'label' => $majority,
            'samples' => count($rows),
            'distribution' => $dist,
        ];

        if (count($dist) <= 1
            || $attributes === []
            || count($rows) < $this->minSamples
            || ($this->maxDepth !== null && $depth >= $this->maxDepth)) {
            return $leaf;
        }

        $best = $this->chooseAttribute($rows, $attributes);
        if ($best === null || $best['gain'] <= 1e-9) {
            return $leaf;
        }

        $remaining = array_values(array_diff($attributes, [$best['attribute']]));
        $children = [];
        foreach (self::partition($rows, $best['attribute']) as $value => $subset) {
            $children[$value] = $this->train($subset, $remaining, $depth + 1);
        }

        return [
            'type' => 'node',
            'attribute' => $best['attribute'],
            'entropy' => round($best['entropy'], 4),
            'gain' => round($best['gain'], 4),
            'gain_ratio' => round($best['gain_ratio'], 4),
            'samples' => count($rows),
            'distribution' => $dist,
            'majority' => $majority,
            'children' => $children,
        ];
    }

    private function chooseAttribute(array $rows, array $attributes): ?array
    {
        $scores = array_map(fn ($a) => self::evaluateAttribute($rows, $a, $this->target), $attributes);

        if ($this->algorithm === 'ID3') {
            usort($scores, fn ($a, $b) => $b['gain'] <=> $a['gain']);

            return $scores[0] ?? null;
        }

        $avgGain = array_sum(array_column($scores, 'gain')) / max(count($scores), 1);
        $candidates = array_filter($scores, fn ($s) => $s['gain'] >= $avgGain - 1e-9);
        usort($candidates, fn ($a, $b) => [$b['gain_ratio'], $b['gain']] <=> [$a['gain_ratio'], $a['gain']]);

        return $candidates[0] ?? null;
    }

    /** Telusuri pohon untuk satu sampel. Nilai yang tak pernah muncul di data latih jatuh ke kelas mayoritas simpul. */
    public static function predict(array $tree, array $sample): array
    {
        $path = [];
        $node = $tree;

        while ($node['type'] === 'node') {
            $value = $sample[$node['attribute']] ?? null;
            if ($value === null || ! isset($node['children'][$value])) {
                $path[] = ['attribute' => $node['attribute'], 'value' => $value, 'fallback' => true];

                return ['label' => $node['majority'], 'path' => $path, 'distribution' => $node['distribution']];
            }
            $path[] = ['attribute' => $node['attribute'], 'value' => $value];
            $node = $node['children'][$value];
        }

        return ['label' => $node['label'], 'path' => $path, 'distribution' => $node['distribution']];
    }

    /** Ubah pohon menjadi aturan IF–THEN. */
    public static function rules(array $tree, array $conditions = []): array
    {
        if ($tree['type'] === 'leaf') {
            return [[
                'conditions' => $conditions,
                'label' => $tree['label'],
                'samples' => $tree['samples'],
                'distribution' => $tree['distribution'],
            ]];
        }

        $rules = [];
        foreach ($tree['children'] as $value => $child) {
            $rules = array_merge($rules, self::rules($child, [...$conditions, [$tree['attribute'], $value]]));
        }

        return $rules;
    }

    public static function depth(array $tree): int
    {
        if ($tree['type'] === 'leaf') {
            return 0;
        }

        return 1 + max(array_map([self::class, 'depth'], $tree['children']));
    }

    public static function evaluate(array $tree, array $rows, string $target, array $classes): array
    {
        $matrix = array_fill_keys($classes, array_fill_keys($classes, 0));
        $correct = 0;

        foreach ($rows as $row) {
            $predicted = self::predict($tree, $row)['label'];
            $actual = $row[$target];
            $matrix[$actual][$predicted] = ($matrix[$actual][$predicted] ?? 0) + 1;
            if ($predicted === $actual) {
                $correct++;
            }
        }

        $perClass = [];
        foreach ($classes as $class) {
            $tp = $matrix[$class][$class] ?? 0;
            $predictedAs = array_sum(array_column($matrix, $class));
            $actualAs = array_sum($matrix[$class] ?? []);
            $precision = $predictedAs ? $tp / $predictedAs : 0;
            $recall = $actualAs ? $tp / $actualAs : 0;
            $perClass[$class] = [
                'precision' => round($precision * 100, 2),
                'recall' => round($recall * 100, 2),
                'f1' => round(($precision + $recall) ? 2 * $precision * $recall / ($precision + $recall) * 100 : 0, 2),
                'support' => $actualAs,
            ];
        }

        return [
            'total' => count($rows),
            'correct' => $correct,
            'accuracy' => count($rows) ? round($correct / count($rows) * 100, 2) : 0,
            'confusion_matrix' => $matrix,
            'per_class' => $perClass,
        ];
    }

    /** Acak deterministik (seed) lalu bagi per kelas agar proporsi kelas tetap terjaga. */
    public static function stratifiedSplit(array $rows, string $target, float $testRatio, int $seed): array
    {
        $byClass = [];
        foreach ($rows as $row) {
            $byClass[$row[$target]][] = $row;
        }

        mt_srand($seed);
        $train = $test = [];
        foreach ($byClass as $group) {
            shuffle($group);
            $nTest = (int) round(count($group) * $testRatio);
            $test = array_merge($test, array_slice($group, 0, $nTest));
            $train = array_merge($train, array_slice($group, $nTest));
        }
        mt_srand();

        return [$train, $test];
    }

    /** k-fold cross validation untuk melihat kestabilan akurasi. */
    public function crossValidate(array $rows, array $attributes, array $classes, int $k, int $seed): array
    {
        mt_srand($seed);
        shuffle($rows);
        mt_srand();

        $folds = array_chunk($rows, (int) ceil(count($rows) / $k));
        $scores = [];
        foreach ($folds as $i => $testFold) {
            $trainRows = array_merge(...array_values(array_diff_key($folds, [$i => true])));
            $tree = $this->train($trainRows, $attributes);
            $scores[] = self::evaluate($tree, $testFold, $this->target, $classes)['accuracy'];
        }

        return [
            'k' => count($folds),
            'scores' => $scores,
            'mean' => round(array_sum($scores) / max(count($scores), 1), 2),
        ];
    }
}
