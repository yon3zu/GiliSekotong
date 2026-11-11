<?php

namespace App\Services;

use App\Models\DecisionTreeModel;
use App\Models\SailingRecord;
use App\Models\SeaCondition;
use App\Support\Sailing;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SailingModelTrainer
{
    public function dataset(): array
    {
        return SailingRecord::query()
            ->get([...Sailing::attributeKeys(), Sailing::TARGET])
            ->map(fn ($r) => $r->only([...Sailing::attributeKeys(), Sailing::TARGET]))
            ->all();
    }

    public function train(string $algorithm, float $testRatio, int $seed, ?int $userId = null, int $minSamples = 2): DecisionTreeModel
    {
        $rows = $this->dataset();
        if (count($rows) < 20) {
            throw new RuntimeException('Data latih minimal 20 baris agar pohon keputusan bermakna.');
        }

        $attributes = Sailing::attributeKeys();
        $engine = new DecisionTree($algorithm, Sailing::TARGET, $minSamples);

        [$trainRows, $testRows] = DecisionTree::stratifiedSplit($rows, Sailing::TARGET, $testRatio, $seed);
        $tree = $engine->train($trainRows, $attributes);
        $evaluation = DecisionTree::evaluate($tree, $testRows, Sailing::TARGET, Sailing::DECISIONS);

        $rootScores = array_map(
            fn ($a) => DecisionTree::evaluateAttribute($trainRows, $a, Sailing::TARGET),
            $attributes
        );

        $metrics = [
            ...$evaluation,
            'test_ratio' => $testRatio,
            'seed' => $seed,
            'min_samples' => $minSamples,
            'depth' => DecisionTree::depth($tree),
            'train_accuracy' => DecisionTree::evaluate($tree, $trainRows, Sailing::TARGET, Sailing::DECISIONS)['accuracy'],
            'cross_validation' => $engine->crossValidate($rows, $attributes, Sailing::DECISIONS, 5, $seed),
            'root_calculation' => [
                'total' => count($trainRows),
                'distribution' => DecisionTree::distribution($trainRows, Sailing::TARGET),
                'entropy' => DecisionTree::entropy($trainRows, Sailing::TARGET),
                'attributes' => $rootScores,
            ],
        ];

        return DB::transaction(function () use ($algorithm, $tree, $metrics, $trainRows, $testRows, $evaluation, $userId) {
            DecisionTreeModel::query()->update(['is_active' => false]);

            $model = DecisionTreeModel::create([
                'algorithm' => $algorithm,
                'tree' => $tree,
                'metrics' => $metrics,
                'rules' => DecisionTree::rules($tree),
                'training_size' => count($trainRows),
                'testing_size' => count($testRows),
                'accuracy' => $evaluation['accuracy'],
                'is_active' => true,
                'trained_by' => $userId,
            ]);

            $this->repredictUpcoming($model);

            return $model;
        });
    }

    public function activate(DecisionTreeModel $model): void
    {
        DB::transaction(function () use ($model) {
            DecisionTreeModel::query()->update(['is_active' => false]);
            $model->update(['is_active' => true]);
            $this->repredictUpcoming($model);
        });
    }

    /** Model baru langsung dipakai untuk memprediksi ulang kondisi laut hari ini dan ke depan. */
    public function repredictUpcoming(DecisionTreeModel $model): void
    {
        SeaCondition::query()->whereDate('date', '>=', today())->get()
            ->each(fn (SeaCondition $c) => $this->applyPrediction($c, $model)->save());
    }

    public function applyPrediction(SeaCondition $condition, ?DecisionTreeModel $model = null): SeaCondition
    {
        $model ??= DecisionTreeModel::active();
        if (! $model) {
            return $condition;
        }

        $result = DecisionTree::predict($model->tree, $condition->only(Sailing::attributeKeys()));
        $condition->prediction = $result['label'];
        $condition->decision_path = $result['path'];
        $condition->model_id = $model->id;

        return $condition;
    }
}
