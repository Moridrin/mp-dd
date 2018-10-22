<?php

namespace mp_dd\Revisions;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use mp_dd\MP_DD;
use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;
use mp_general\Revisions\InstallRevision;

abstract class R1 implements InstallRevision
{
    public static function install(\Plugin_Upgrader $tmp): void
    {
        if (!BaseFunctions::endsWith(MP_DD::INSTALL_FILE, $tmp->result['destination'])) {
            return;
        }
        global $wpdb;
        $posts          = get_posts(['post_type' => get_post_types(), 'post_status' => ['publish', 'draft'], 'posts_per_page' => -1]);
        $internalErrors = libxml_use_internal_errors(true);
        foreach ($posts as $post) {
            if ($post->post_content) {
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadHTML(utf8_decode($post->post_content));
                $xpath = new DOMXPath($dom);
                $nodes = $xpath->query('//span[@data-view-right]');
                self::replaceNode($dom, $nodes);
                $nodes = $xpath->query('//div[@data-view-right]');
                self::replaceNode($dom, $nodes);
                $nodes = $xpath->query('//p[@data-view-right]');
                self::replaceNode($dom, $nodes);
                $html = '';
                foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
                    $html .= html_entity_decode($dom->saveHTML($node));
                }
                $wpdb->update($wpdb->posts, ['post_content' => $html], ['ID' => $post->ID]);
            }
        }
        libxml_use_internal_errors($internalErrors);
        SSV_Global::revisionInstalled(MP_DD::INSTALL_FILE, 1);
    }

    private static function replaceNode(DOMDocument $dom, DOMNodeList &$nodes)
    {
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $newNode = '[view-right';
            $newNode .= ' right="' . $node->getAttribute('data-view-right') . '"';
            if ($node->hasAttribute('data-placeholder')) {
                $newNode .= ' placeholder="' . $node->getAttribute('data-placeholder') . '"';
            }
            $newNode .= ']' . implode(array_map([$node->ownerDocument, "saveHTML"], iterator_to_array($node->childNodes))) . '[/view-right]';
            $node->parentNode->replaceChild($dom->createTextNode($newNode), $node);
        }
    }
}
add_action('upgrader_process_complete', [R1::class, 'install']);
