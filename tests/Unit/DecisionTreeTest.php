<?php

namespace Tests\Unit;

use App\Services\DecisionTree;
use PHPUnit\Framework\TestCase;

/** Diuji dengan dataset klasik "Play Tennis" (Quinlan, 1986) yang nilainya sudah dikenal. */
class DecisionTreeTest extends TestCase
{
    private function tennis(): array
    {
        $raw = [
            ['Sunny', 'Hot', 'High', 'Weak', 'No'], ['Sunny', 'Hot', 'High', 'Strong', 'No'],
            ['Overcast', 'Hot', 'High', 'Weak', 'Yes'], ['Rain', 'Mild', 'High', 'Weak', 'Yes'],
            ['Rain', 'Cool', 'Normal', 'Weak', 'Yes'], ['Rain', 'Cool', 'Normal', 'Strong', 'No'],
            ['Overcast', 'Cool', 'Normal', 'Strong', 'Yes'], ['Sunny', 'Mild', 'High', 'Weak', 'No'],
            ['Sunny', 'Cool', 'Normal', 'Weak', 'Yes'], ['Rain', 'Mild', 'Normal', 'Weak', 'Yes'],
            ['Sunny', 'Mild', 'Normal', 'Strong', 'Yes'], ['Overcast', 'Mild', 'High', 'Strong', 'Yes'],
            ['Overcast', 'Hot', 'Normal', 'Weak', 'Yes'], ['Rain', 'Mild', 'High', 'Strong', 'No'],
        ];

        return array_map(fn ($r) => array_combine(['outlook', 'temp', 'humidity', 'wind', 'play'], $r), $raw);
    }

    public function test_entropy_and_gain_match_textbook_values(): void
    {
        $rows = $this->tennis();

        $this->assertEqualsWithDelta(0.940, DecisionTree::entropy($rows, 'play'), 0.001);
        $this->assertEqualsWithDelta(0.247, DecisionTree::evaluateAttribute($rows, 'outlook', 'play')['gain'], 0.001);
        $this->assertEqualsWithDelta(0.048, DecisionTree::evaluateAttribute($rows, 'wind', 'play')['gain'], 0.001);
        $this->assertEqualsWithDelta(1.577, DecisionTree::evaluateAttribute($rows, 'outlook', 'play')['split_info'], 0.001);
    }

    public function test_id3_and_c45_pick_outlook_as_root_and_fit_training_data(): void
    {
        $rows = $this->tennis();
        $attrs = ['outlook', 'temp', 'humidity', 'wind'];

        foreach (['ID3', 'C4.5'] as $algo) {
            $tree = (new DecisionTree($algo, 'play', 1))->train($rows, $attrs);
            $this->assertSame('outlook', $tree['attribute']);
            $this->assertSame(100.0, (float) DecisionTree::evaluate($tree, $rows, 'play', ['Yes', 'No'])['accuracy']);
        }
    }

    public function test_predict_returns_path_and_falls_back_on_unseen_value(): void
    {
        $tree = (new DecisionTree('C4.5', 'play', 1))->train($this->tennis(), ['outlook', 'temp', 'humidity', 'wind']);

        $result = DecisionTree::predict($tree, ['outlook' => 'Sunny', 'temp' => 'Hot', 'humidity' => 'High', 'wind' => 'Weak']);
        $this->assertSame('No', $result['label']);
        $this->assertSame('outlook', $result['path'][0]['attribute']);

        $fallback = DecisionTree::predict($tree, ['outlook' => 'Foggy']);
        $this->assertSame('Yes', $fallback['label']);
        $this->assertTrue($fallback['path'][0]['fallback']);
    }

    public function test_rules_cover_every_leaf(): void
    {
        $tree = (new DecisionTree('ID3', 'play', 1))->train($this->tennis(), ['outlook', 'temp', 'humidity', 'wind']);

        $this->assertSame(14, array_sum(array_column(DecisionTree::rules($tree), 'samples')));
    }
}
