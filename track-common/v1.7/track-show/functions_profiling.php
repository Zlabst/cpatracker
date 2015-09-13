<?php
function _profiling_start_step($step_id)
{
    // Run this to start profiling for a code block.
    // Don't forget to use _profiling_end_step as end of block
    // Results will be saved in /cache/profiling if _ENABLE_PROFILING is true
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