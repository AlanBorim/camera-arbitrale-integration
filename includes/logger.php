<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cai_log( $mensagem ) {
    $arquivo_log = CAI_PLUGIN_DIR . 'integracao.log';
    $data = date( 'Y-m-d H:i:s' );
    $log  = "[{$data}] {$mensagem}\n";
    file_put_contents( $arquivo_log, $log, FILE_APPEND );
}
