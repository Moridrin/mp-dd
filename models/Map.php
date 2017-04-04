<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 7:56
 */
class Map extends EmbeddedObject
{
    #region Constants
    const WALL_TYPES
        = array(
            'Rock (DC 10 to climb)',
            'Masonry (DC 15 to climb)',
            'Superior Masonry (DC 20 to climb)',
        );
    const FLOOR_TYPES
        = array(
            'Rock',
            'Masonry',
            'Superior Masonry',
            'Sand',
            'Water',
            'Vegetation',
        );
    const TEMPERATURES
        = array(
            'Freezing',
            'Cold',
            'Cool',
            'Normal',
            'Warm',
            'Hot',
            'Burning',
        );
    const ILLUMINATION
        = array(
            'Dark (individual creatures may carry lights)',
            'Average (shadowy in corridors, lamps or torches in most rooms)',
            'Bright (lamps or torches every 40 ft.)',
        );
    #endregion

    #region Variables
    public $history;
    public $wallType;
    public $floorType;
    public $temperature;
    public $illumination;

    protected $map;

    public $rooms = array();
    public $encounters = array();

    #endregion

    public function __construct()
    {
        $this->wallType     = 1;
        $this->floorType    = 1;
        $this->temperature  = 3;
        $this->illumination = 1;
    }

    public function getGeneralEditor()
    {
        ob_start();
        ?>
        <table class="form-table">
            <tr>
                <th><label for="history">History</label></th>
                <td><textarea type="text" name="history" id="history" class="regular-text"><?= $this->history ?></textarea></td>
            </tr>
            <tr>
                <th><label for="wall_type">Walls</label></th>
                <td>
                    <select id="wall_type" name="wall_type">
                        <option value="0" <?= selected($this->wallType, 0) ?>>Rock (DC 10 to climb)</option>
                        <option value="1" <?= selected($this->wallType, 1) ?>>Masonry (DC 15 to climb)</option>
                        <option value="2" <?= selected($this->wallType, 2) ?>>Superior Masonry (DC 20 to climb)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="floor_type">Floor</label></th>
                <td>
                    <select id="floor_type" name="floor_type">
                        <option value="0" <?= selected($this->floorType, 0) ?>>Rock</option>
                        <option value="1" <?= selected($this->floorType, 1) ?>>Masonry</option>
                        <option value="2" <?= selected($this->floorType, 2) ?>>Superior Masonry</option>
                        <option value="3" <?= selected($this->floorType, 3) ?>>Sand</option>
                        <option value="4" <?= selected($this->floorType, 4) ?>>Water</option>
                        <option value="5" <?= selected($this->floorType, 5) ?>>Vegetation</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="temperature">Temperature</label></th>
                <td>
                    <select id="temperature" name="temperature">
                        <option value="0" <?= selected($this->temperature, 0) ?>>Freezing</option>
                        <option value="1" <?= selected($this->temperature, 1) ?>>Cold</option>
                        <option value="2" <?= selected($this->temperature, 2) ?>>Cool</option>
                        <option value="3" <?= selected($this->temperature, 3) ?>>Normal</option>
                        <option value="4" <?= selected($this->temperature, 4) ?>>Warm</option>
                        <option value="5" <?= selected($this->temperature, 5) ?>>Hot</option>
                        <option value="6" <?= selected($this->temperature, 6) ?>>Burning</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="illumination">Illumination</label></th>
                <td>
                    <select id="illumination" name="illumination">
                        <option value="0" <?= selected($this->illumination, 0) ?>>Dark (individual creatures may carry lights)</option>
                        <option value="1" <?= selected($this->illumination, 1) ?>>Average (shadowy in corridors, lamps or torches in most rooms)</option>
                        <option value="2" <?= selected($this->illumination, 2) ?>>Bright (lamps or torches every 40 ft.)</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

    public function getMapEditor()
    {
        ob_start();
        ?>
        <table class="form-table">
            <tr>
                <th><label for="map">Map</label></th>
                <td><textarea type="text" name="map" id="map" class="html-active"><?= $this->map ?></textarea></td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

    public function getHTML($description)
    {
        ob_start();
        echo($this->map);
        ?>
        <ul class="collapsible" id="test" data-collapsible="expandable">
            <li>
                <div class="collapsible-header active"><i class="material-icons">account_balance</i>General</div>
                <div class="collapsible-body">
                    <table class="section">
                        <tr>
                            <th class="key">Dungeon History</th>
                            <td class="value"><?= $this->history ?></td>
                        </tr>
                        <tr>
                            <th class="key">Dungeon Walls</th>
                            <td class="value"><?= self::WALL_TYPES[$this->wallType] ?></td>
                        </tr>
                        <tr>
                            <th class="key">Dungeon Floor</th>
                            <td class="value"><?= self::FLOOR_TYPES[$this->floorType] ?></td>
                        </tr>
                        <tr>
                            <th class="key">Temperature</th>
                            <td class="value"><?= self::TEMPERATURES[$this->temperature] ?></td>
                        </tr>
                        <tr>
                            <th class="key">Illumination</th>
                            <td class="value"><?= self::ILLUMINATION[$this->illumination] ?></td>
                        </tr>
                    </table>
                </div>
            </li>
            <li>
                <div class="collapsible-header active"><i class="material-icons">pets</i>Monsters</div>
                <div class="collapsible-body">
                    <table class="section">
                        <tr>
                            <th>Monster</th>
                            <th>Purpose</th>
                            <th>4 Players</th>
                            <th>5 Players</th>
                            <th>6 Players</th>
                            <th>XP</th>
                        </tr>
                        <tr>
                            <td>1x <a href="http://www.aidedd.org/dnd/monstres.php?vo=Nothic" target="_blank">Nothic</a></td>
                            <td>Bloodied and fleeing a more powerful enemy</td>
                            <td>Die</td>
                            <td>Hard</td>
                            <td>Hard</td>
                            <td>450 XP</td>
                        </tr>
                        <tr>
                            <td>2x <a href="http://www.aidedd.org/dnd/monstres.php?vo=Piercer" target="_blank">Piercer</a></td>
                            <td>Hunting for food</td>
                            <td>Hard</td>
                            <td>Medium</td>
                            <td>Medium</td>
                            <td>200 XP</td>
                        </tr>
                        <tr>
                            <td>
                                2x <a href="http://www.aidedd.org/dnd/monstres.php?vo=Darkmantle" target="_blank">Darkmantle</a><br/>
                                1x <a href="http://www.aidedd.org/dnd/monstres.php?vo=Giant-Bat" target="_blank">Giant Bat</a>
                            </td>
                            <td>Sleeping<br/>Sleeping</td>
                            <td>Hard</td>
                            <td>Hard</td>
                            <td>Medium</td>
                            <td>250 XP</td>
                        </tr>
                        <tr>
                            <td>1x <a href="http://www.aidedd.org/dnd/monstres.php?vo=Giant-Spider" target="_blank">Giant Spider</a></td>
                            <td>Hunting for food</td>
                            <td>Medium</td>
                            <td>Easy</td>
                            <td>Easy</td>
                            <td>200 XP</td>
                        </tr>
                    </table>
                </div>
            </li>
        </ul>
        <?php
        return ob_get_clean() . $description;
    }

    public function setMap($map)
    {
        $this->map = $map;
    }
}
