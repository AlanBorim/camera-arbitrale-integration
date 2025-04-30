<?php
/*
Plugin Name: Camera Arbitrale Integration
Description: Integração entre o site camera-arbitrale.it e LearnPress para matrícula automática de alunos.
Version: 1.0.0
Author: Alan Borim
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita acesso direto
}

define( 'CAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Inclui arquivos necessários
require_once CAI_PLUGIN_DIR . 'includes/endpoints.php';
require_once CAI_PLUGIN_DIR . 'includes/helpers.php';
require_once CAI_PLUGIN_DIR . 'includes/logger.php';


// Admin config
if (is_admin()) {
    require_once CAI_PLUGIN_DIR . 'includes/admin-page.php';
}
