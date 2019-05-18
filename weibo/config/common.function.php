<?php

    function getTodayTimestamp()
    {
        return strtotime(date('Y-m-d', time()).' 00:00:00');
    }

    function loginLogToArray($strLog)
    {
        return json_encode($strLog, true);
    }