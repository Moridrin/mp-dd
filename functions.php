<?php

function mp_dd_parser_session()
{
    require_once "Parser/Wizardawn/Models/JsonObject.php";
    require_once "Parser/Wizardawn/Models/City.php";
    require_once "Parser/Wizardawn/Models/NPC.php";
    require_once "Parser/Wizardawn/Models/Map.php";
    require_once "Parser/Wizardawn/Models/MapPanel.php";
    require_once "Parser/Wizardawn/Models/MapLabel.php";
    require_once "Parser/Wizardawn/Models/Building.php";
    require_once "Parser/Wizardawn/Models/NPC.php";
    require_once "Parser/Wizardawn/Models/Product.php";
    require_once "Parser/Wizardawn/Models/Spell.php";
    require_once "Parser/Wizardawn/Models/VaultItem.php";
}

add_action('before_session_start', 'mp_dd_parser_session', 1);
