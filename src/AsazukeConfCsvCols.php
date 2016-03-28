<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeConfCsvCols extends AsazukeConfGeneral
{
    public static $csv_cols = [
        // '列名' => 'CSSセレクタ',{}内に記述すると、固定値を設定できます。
        '* path' => '',
        '* content' => '',
        '* id' => '',
        '* title' => 'title',
        // '* title_breadcrumb' => 'header .breadcrumb ul li:last-child span',
        '* title_breadcrumb' => '[class*="breadcrumbs"] li:last-child span',
        '* title_h1' => 'h1',
        '* title_label' => '',
        '* title_full' => '',
        '* logical_path' => 'header .breadcrumb ul li a',
        // '* logical_path' => '[class*="breadcrumbs"]',
        '* list_flg' => '{1}',
        '* layout' => '',
        '* orderby' => '',
        '* keywords' => 'meta[name="keywords"]',
        '* description' => 'meta[name="description"]',
        '* category_top_flg' => '',
        '* **delete_flg' => ''
        // ↓↓↓optional
        ,
        '* og:title' => 'meta[property="og:title"]',
        '* og:description' => 'meta[property="og:description"]',
        '* og:image' => 'meta[property="og:image"]',
        '* og:type' => 'meta[property="og:type"]',
        '* og:site_name' => 'meta[property="og:site_name"]',
        '* og:url' => 'meta[property="og:url"]',
        '* copyright' => 'meta[name="copyright"]',
        '* viewport' => 'meta[name="viewport"]',

        '* sitecatalyst1' => 'script[src*="footer"]',
        '* sitecatalyst2' => 'script',

        '* favicon' => 'link[rel="icon"]'
    ];
    
    // WEBスクレイピングの設定
    public static $export_html = [
        [
            'name' => 'outerHTMLのテスト',
            'selector' => '.contents',
            'scope' => 'outerHTML'
        ]
        // ,
        // [
        // 'name' => 'innerHTMLのテスト',
        // 'selector' => '.contents',
        // 'scope' => 'innerHTML'
        // ]
    ];
}
