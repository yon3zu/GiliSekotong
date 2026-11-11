<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DecisionTreeModel;
use App\Models\SailingRecord;
use App\Services\DecisionTree;
use App\Services\SailingModelTrainer;
use App\Support\Sailing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class DecisionTreeController extends Controller
{
    public function index()
    {
        return $this->render(DecisionTreeModel::active());
    }

    public function show(DecisionTreeModel $model)
    {
        return $this->render($model);
    }

    private function render(?DecisionTreeModel $model)
    {
        return view('admin.tree.index', [
            'model' => $model,
            'history' => DecisionTreeModel::query()->with('trainer')->latest()->take(10)->get(),
            'datasetSize' => SailingRecord::query()->count(),
            'classCounts' => SailingRecord::query()->selectRaw('keputusan, count(*) as total')->groupBy('keputusan')->pluck('total', 'keputusan'),
        ]);
    }

    public function train(Request $request, SailingModelTrainer $trainer)
    {
        $data = $request->validate([
            'algorithm' => ['required', Rule::in(['C4.5', 'ID3'])],
            'test_ratio' => ['required', 'numeric', 'between:0.1,0.5'],
            'seed' => ['required', 'integer', 'min:1', 'max:99999'],
            'min_samples' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        try {
            $model = $trainer->train($data['algorithm'], (float) $data['test_ratio'], (int) $data['seed'], $request->user()->id, (int) $data['min_samples']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['train' => $e->getMessage()]);
        }

        return redirect()->route('admin.tree.index')
            ->with('success', "Model {$model->algorithm} dilatih ulang. Akurasi data uji {$model->accuracy}%.");
    }

    public function activate(DecisionTreeModel $model, SailingModelTrainer $trainer)
    {
        $trainer->activate($model);

        return redirect()->route('admin.tree.index')->with('success', 'Model dipakai sebagai acuan prediksi.');
    }

    public function simulate(Request $request)
    {
        $rules = [];
        foreach (Sailing::ATTRIBUTES as $key => $attr) {
            $rules[$key] = ['required', Rule::in($attr['values'])];
        }
        $sample = $request->validate($rules);

        $model = DecisionTreeModel::active();
        abort_unless($model, 404);

        return back()->withInput()->with('simulation', DecisionTree::predict($model->tree, $sample));
    }
}
