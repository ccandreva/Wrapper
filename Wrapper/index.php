<?php

/*******************************************************
    This file is for the old module load routine
    You can now link the module in a menu using the
    module name enclosed with brackets
    (ex: [NukeWrapper])
 *******************************************************/
if (!defined("LOADED_AS_MODULE")) { echo 'You may not access this module directly'; }
else { pnRedirect(pnModURL(pnVarCleanFromInput('name'), 'user', 'main')); }
?>
