<?php

/**
 * @file plugins/oaiMetadataFormats/dialnet/index.php
 *
 *
 * @ingroup oai_format_dialnet
 * @brief Wrapper for the OAI DIALNET format plugin.
 *
 */

require_once('OAIMetadataFormatPlugin_dialnet.inc.php');
require_once('OAIMetadataFormat_dialnet.inc.php');

return new OAIMetadataFormatPlugin_dialnet();

?>
