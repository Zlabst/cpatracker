<?php
function _profiling_start_step($step_id)
{
    global $_PROFILING_arr_steps;
    if (empty($_PROFILING_arr_steps[$step_id]))
    {
        $_PROFILING_arr_steps[$step_id]='';
        $_PROFILING_arr_steps[$step_id]['start']=microtime(true);
        $_PROFILING_arr_steps[$step_id]['total']=0;
    }
    else
    {
        $_PROFILING_arr_steps[$step_id]['start']=microtime(true);
    }
}
function _profiling_end_step($step_id)
{
    global $_PROFILING_arr_steps;
    $_PROFILING_arr_steps[$step_id]['total']+=(microtime(true)-$_PROFILING_arr_steps[$step_id]['start']);
}