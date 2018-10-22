<?php

namespace mp_dd\shortcodes;

use mp_general\base\BaseFunctions;

abstract class Calendar
{

    public static function calendar($attributes, $innerHtml)
    {
        $lunarPhases   = [
            'New Moon',
            'Waxing Crescent',
            'First Quarter',
            'Waxing Gibbous',
            'Full Moon',
            'Waning Gibbous',
            'Last Quarter',
            'Waning Crescent',
        ];
        $weekDays      = [
            'Wonday',
            'Dalday',
            'Morboday',
            'Rahlday',
            'Devday',
        ];
        $months        = [
            ['name' => 'January', 'days' => 12],
            ['name' => 'February', 'days' => 20],
            ['name' => 'March', 'days' => 4],
            ['name' => 'April', 'days' => 29],
            ['name' => 'May', 'days' => 10],
            ['name' => 'June', 'days' => 24],
            ['name' => 'July', 'days' => 6],
            ['name' => 'August', 'days' => 15],
            ['name' => 'September', 'days' => 4],
            ['name' => 'October', 'days' => 18],
            ['name' => 'November', 'days' => 11],
            ['name' => 'December', 'days' => 20],
        ];
        $moons         = [
            ['name' => 'Elmon', 'cicle' => 11, 'start' => 0],
            ['name' => 'Elralei', 'cicle' => 27, 'start' => 3],
        ];
        $year          = 2018;
        $day           = 0;
        $monthStartsOn = 1;
        ob_start();
        ?>
        <style>
            .lunar {
                display: block;
                width: 32px;
                height: 32px;
                background: url(https://moridrin.com/wp-content/uploads/2018/08/Phases-of-the-Moon.png);
                text-indent: -1000px;
                overflow: hidden;
            }

            .lunar.phase-0 {
                background-position: 0;
            }

            .lunar.phase-1 {
                background-position: -128px 0;
            }

            .lunar.phase-2 {
                background-position: -256px 0;
            }

            .lunar.phase-3 {
                background-position: -384px 0;
            }

            .lunar.phase-4 {
                background-position: -512px 0;
            }

            .lunar.phase-5 {
                background-position: -640px 0;
            }

            .lunar.phase-6 {
                background-position: -768px 0;
            }

            .lunar.phase-7 {
                background-position: -896px 0;
            }

            .wday {
                width: 150px;
                height: 180px;
                vertical-align: top;
                background-color: rgba(255, 255, 255, 0.2);
            }

            .mday, .note {
                padding: 5px;
                z-index: 100;
                position: relative;
                color: black;
            }

            .moons {
                z-index: 100;
                position: relative;
            }

            .wday a {
                background-color: #9E9E9E;
                position: absolute;
                width: 150px;
                height: 150px;
            }

            .wday a .overlay {
                background-color: rgba(255, 255, 255, 0.4);
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        </style>
        <table id="calendar" class="calendar">
            <tbody class="block">
            <?php foreach ($months as $month => $data) {
                $monthEnded  = false;
                $monthName   = $data['name'];
                $daysInMonth = $data['days'];
                ?>
                <tr>
                    <td>
                        <table style="table-layout: fixed;">
                            <tbody>
                            <tr>
                                <td colspan="5" class="center"><h2><?=BaseFunctions::escape($monthName, 'html')?></h2></td>
                            </tr>
                            <tr>
                                <?php foreach ($weekDays as $weekDayName): ?>
                                    <th><?=BaseFunctions::escape($weekDayName, 'html')?></th>
                                <?php endforeach; ?>
                            </tr>
                            <?php
                            $monthDay = 1;
                            while ($monthDay <= $daysInMonth) {
                                ?>
                                <tr>
                                    <?php
                                    foreach ($weekDays as $dayOfWeek => $weekDayName) {
                                        if ($monthDay === 1 && $dayOfWeek < $monthStartsOn) {
                                            ?>
                                            <td colspan="1"></td>
                                            <?php
                                        } elseif ($monthDay > $daysInMonth) {
                                            if (!$monthEnded) {
                                                $monthEnded    = true;
                                                $monthStartsOn = $dayOfWeek;
                                            }
                                            ?>
                                            <td colspan="1"></td>
                                            <?php
                                        } else {
                                            $args  = [
                                                'date_query' => [
                                                    [
                                                        'year' => $year,
                                                        'month' => $month+1,
                                                        'day' => $monthDay,
                                                        'limit'     => 1,
                                                    ],
                                                ],
                                            ];
                                            $query = new \WP_Query($args);
                                            $posts = $query->get_posts();
                                            ?>
                                            <td class="wday">
                                                <?php
                                                if (!empty($posts)) {
                                                    $url = get_the_post_thumbnail_url($posts[0]->ID, 'thumbnail');
                                                    ?>
                                                    <a href="<?=get_permalink($posts[0]->ID)?>" style="background: url(<?=$url?>);">
                                                        <div class="overlay"></div>
                                                        <div class="mday">Day:<?=$monthDay?></div>
                                                        <div class="moons">
                                                            <?php foreach ($moons as $moon => $moonData): ?>
                                                                <?php $moonName = $moonData['name'] ?>
                                                                <?php $moonPhase = ($day + $moonData['start']) % $moonData['cicle'] ?>
                                                                <?php $moonPhase = intval(($moonPhase / $moonData['cicle']) * 8) ?>
                                                                <div title="<?=BaseFunctions::escape($moonName, 'attr')?>, <?=$lunarPhases[(($moonPhase / $moonData['cicle']) * 7)]?>" class="lunar phase-<?=$moonPhase?>">
                                                                    <?=BaseFunctions::escape($moonName, 'html')?>, <?=$lunarPhases[$moonPhase]?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div id="note-<?=BaseFunctions::escape($year, 'attr')?>-<?=BaseFunctions::escape($month, 'attr')?>-<?=BaseFunctions::escape($day, 'attr')?>" class="note" data-ymd="<?=BaseFunctions::escape($year, 'attr')?>-<?=BaseFunctions::escape($month, 'attr')?>-<?=BaseFunctions::escape($day, 'attr')?>">
                                                            <?=BaseFunctions::escape($posts[0]->post_title, 'html')?>
                                                        </div>
                                                    </a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <div class="mday">Day:<?=$monthDay?></div>
                                                    <div class="moons">
                                                        <?php foreach ($moons as $moon => $moonData): ?>
                                                            <?php $moonName = $moonData['name'] ?>
                                                            <?php $moonPhase = ($day + $moonData['start']) % $moonData['cicle'] ?>
                                                            <?php $moonPhase = intval(($moonPhase / $moonData['cicle']) * 8) ?>
                                                            <div title="<?=BaseFunctions::escape($moonName, 'attr')?>, <?=$lunarPhases[(($moonPhase / $moonData['cicle']) * 7)]?>" class="lunar phase-<?=$moonPhase?>">
                                                                <?=BaseFunctions::escape($moonName, 'html')?>, <?=$lunarPhases[$moonPhase]?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                            <?php
                                            ++$day;
                                            ++$monthDay;
                                        }
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php
                if (!$monthEnded) {
                    $monthStartsOn = 0;
                }
            }
            ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public static function filterAllTags($content)
    {
        do_shortcode($content);
    }

    public static function duplicatePrefixes($content)
    {
        $pattern = get_shortcode_regex(['mmap']);

        if (
            preg_match_all('/'.$pattern.'/s', $content, $matches)
            && array_key_exists(2, $matches)
            && in_array('mmap', $matches[2])
        ) {
            for ($i = 0; $i < count($matches[5]); ++$i) {
                $tmpString = '[#####'.uniqid().'#####]';
                $content   = str_replace($matches[0][$i], $tmpString, $content);
                $content   = str_replace($matches[5][$i], $matches[0][$i], $content);
                $content   = str_replace($tmpString, $matches[0][$i], $content);
            }
        }
        return $content;
    }
}

add_shortcode('calendar', [Calendar::class, 'calendar']);
add_shortcode('calendar', [Calendar::class, 'calendar']);
add_filter('the_content', [Calendar::class, 'duplicatePrefixes']);
