<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLDataLoop extends Model
{
    protected $table      = 'sl_data_loop';
    protected $primaryKey = 'data_loop_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
        'data_loop_tree', 
        'data_loop_root', 
        'data_loop_plural', 
        'data_loop_singular', 
        'data_loop_table', 
        'data_loop_sort_fld', 
        'data_loop_done_fld', 
        'data_loop_max_limit', 
        'data_loop_warn_limit', 
        'data_loop_min_limit', 
        'data_loop_is_step', 
        'data_loop_auto_gen', 
    ];
    
    
    public $conds = [];
    
    public function loadLoopConds()
    {
        $this->conds = [];
        $getConds = SLConditionsNodes::where('cond_node_loop_id', $this->data_loop_id)
            ->get();
        if ($getConds->isNotEmpty()) {
            foreach ($getConds as $c) {
                $cond = SLConditions::find($c->cond_node_cond_id);
                $cond->loadVals();
                $this->conds[] = $cond;
            }
        }
        return true;
    }
    
}
