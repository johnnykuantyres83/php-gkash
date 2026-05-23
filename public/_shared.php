<?php

require_once dirname(__DIR__) . '/bootstrap.php';

function gkash_runtime()
{
    static $runtime = null;

    if ($runtime !== null) {
        return $runtime;
    }

    $config = gkash_config();
    $logger = new Support\Logger($config['log_path']);
    $store = new Support\OrderStore($config['cache_path'] . '/orders');
    $client = new GKashClient($config, $logger);
    $checkout = new GKashCheckout($client, $logger, $store);
    $handler = new GKashCallbackHandler($client, $logger, $store);

    $runtime = array(
        'config' => $config,
        'logger' => $logger,
        'store' => $store,
        'client' => $client,
        'checkout' => $checkout,
        'handler' => $handler,
    );

    return $runtime;
}

function gkash_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function gkash_render_page($title, $content, $extraHead = '')
{
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . gkash_h($title) . '</title>';
    echo '<style>
        :root{--bg:#f4efe7;--panel:#fffaf1;--ink:#1b1a17;--muted:#6e6254;--line:#e6d9c6;--accent:#19324d;--accent2:#b35c2e;}
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(180deg,#f8f3ea,#efe6d8 60%,#eadfce);color:var(--ink);}
        a{color:var(--accent)}
        .shell{max-width:1120px;margin:0 auto;padding:32px 18px 64px}
        .hero{display:grid;gap:18px;grid-template-columns:1.4fr .8fr;align-items:start}
        .card{background:var(--panel);border:1px solid var(--line);border-radius:18px;box-shadow:0 14px 38px rgba(35,28,16,.08);padding:24px}
        .title{font-size:42px;line-height:1.05;margin:0 0 10px;letter-spacing:-.03em}
        .sub{color:var(--muted);font-size:16px;line-height:1.6}
        .tag{display:inline-block;background:#18324d;color:#fff;border-radius:999px;padding:7px 12px;font-size:12px;letter-spacing:.08em;text-transform:uppercase}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .field{display:flex;flex-direction:column;gap:6px}
        label{font-size:13px;color:var(--muted)}
        input,select,textarea{width:100%;border:1px solid var(--line);border-radius:12px;padding:12px 14px;background:#fff;font-size:15px;color:var(--ink)}
        textarea{min-height:94px;resize:vertical}
        .row{display:flex;gap:12px;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:12px 18px;font-weight:700;text-decoration:none;cursor:pointer}
        .btn.primary{background:var(--accent);color:#fff}
        .btn.secondary{background:#e9dfcf;color:var(--ink)}
        .btn.warn{background:var(--accent2);color:#fff}
        .meta{font-size:13px;color:var(--muted)}
        .table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:11px 12px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
        .table th{width:220px;color:var(--muted);font-weight:600}
        pre{white-space:pre-wrap;word-break:break-word;background:#f8f4ed;padding:14px;border-radius:12px;border:1px solid var(--line);margin:0}
        .notice{padding:14px 16px;border-radius:12px;background:#eef4fa;border:1px solid #c9d7e6;color:#1d405f}
        .ok{background:#eef8ef;border-color:#cde3cf;color:#295235}
        .fail{background:#fdf1ef;border-color:#ecc8c1;color:#6d2a1e}
        .footer{margin-top:24px;color:var(--muted);font-size:13px}
        @media (max-width:900px){.hero{grid-template-columns:1fr}.title{font-size:34px}.grid{grid-template-columns:1fr}.table th{width:42%}}
    </style>' . $extraHead;
    echo '</head>';
    echo '<body>';
    echo '<div class="shell">';
    echo $content;
    echo '</div>';
    echo '</body>';
    echo '</html>';
}

function gkash_render_kv_table(array $values)
{
    $html = '<table class="table">';
    foreach ($values as $label => $value) {
        $html .= '<tr><th>' . gkash_h($label) . '</th><td>' . $value . '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}

function gkash_render_json_pre($value)
{
    if (is_array($value) || is_object($value)) {
        $value = json_encode($value, JSON_PRETTY_PRINT);
    }

    return '<pre>' . gkash_h($value) . '</pre>';
}

function gkash_request_order_id()
{
    if (isset($_REQUEST['order_id'])) {
        return gkash_sanitize_text($_REQUEST['order_id']);
    }

    if (isset($_REQUEST['cartid'])) {
        return gkash_sanitize_text($_REQUEST['cartid']);
    }

    if (isset($_REQUEST['v_cartid'])) {
        return gkash_sanitize_text($_REQUEST['v_cartid']);
    }

    return '';
}

