<?php

/**
 * @file plugins/oaiMetadataFormats/dialnet/OAIMetadataFormatPlugin_dialnet.inc.php
 *
 *
 * @class OAIMetadataFormatPlugin_dialnet
 * @ingroup oai_format_dialnet
 * @see OAI
 *
 * @brief Dialnet Journal Article metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_dialnet extends OAIMetadataFormatPlugin {
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'OAIMetadataFormatPlugin_dialnet';
	}

	function getDisplayName() {
		return __('plugins.oaiMetadata.dialnet.displayName');
	}

	function getDescription() {
		return __('plugins.oaiMetadata.dialnet.description');
	}

	function getFormatClass() {
		return 'OAIMetadataFormat_dialnet';
	}

	function getMetadataPrefix() {
		return 'dialnet';
	}

	function getSchema() {
		return 'http://dialnet.unirioja.es DialnetSchema.xsd';
	}

	function getNamespace() {
		return 'http://dialnet.unirioja.es';
	}
}

?>
