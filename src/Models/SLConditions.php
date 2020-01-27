<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\SLConditionsVals;

class SLConditions extends Model
{
    protected $table         = 'sl_conditions';
    protected $primaryKey     = 'cond_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'cond_database', 
        'cond_tag', 
        'cond_desc', 
        'cond_operator', 
        'cond_oper_deet', 
        'cond_field', 
        'cond_table', 
        'cond_loop', 
        'cond_opts', 
    ];
    
    public $condVals             = [];
    public $condFldResponses     = [];
    
    public function loadVals()
    {
        $this->condVals = [];
        $chk = SLConditionsVals::where('cond_val_cond_id', $this->cond_id)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $v) {
                $this->condVals[] = trim($v->cond_val_value);
            }
        }
        
        $this->condFldResponses = $GLOBALS["SL"]->getFldResponsesByID($this->cond_field);
        
        if (sizeof($this->condVals) > 0) {
            if (sizeof($this->condFldResponses["vals"]) == 0) {
                foreach ($this->condVals as $j => $val) {
                    $this->condFldResponses["vals"][] = [ $val, $val ];
                }
            }
            foreach ($this->condVals as $j => $val) {
                $def = $GLOBALS["SL"]->def->getValById(intVal($val));
                $found = false;
                foreach ($this->condFldResponses["vals"] as $k => $valInfo) {
                    if ($valInfo[0] == $val) {
                        $found = true;
                        if (strlen($valInfo[1]) > 40) {
                            if ($def != '') {
                                $this->condFldResponses["vals"][$k][1] = $def;
                            } else {
                                $this->condFldResponses["vals"][$k][1] = $val;
                            }
                        }
                        if ($this->condFldResponses["vals"][$k][0] 
                                == $this->condFldResponses["vals"][$k][1]
                            && $def != '') {
                            $this->condFldResponses["vals"][$k][1] = $def;
                        }
                    }
                }
                if (!$found && $def != '') {
                    $this->condFldResponses["vals"][] = [ $val, $def ];
                }
            }
        }
        return true;
    }
    
    
    public function tblName()
    {
        return $GLOBALS["SL"]->tbl[$this->cond_table];
    }
    
}
