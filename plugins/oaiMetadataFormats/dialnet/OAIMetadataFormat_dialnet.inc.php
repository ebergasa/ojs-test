<?php

/**
 * @file plugins/oaiMetadataFormats/dialnet/OAIMetadataFormat_NLM.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_dialnet
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- Dialnet
 */


class OAIMetadataFormat_dialnet extends OAIMetadataFormat {

	/**
	 * @see OAIMetadataFormat#toXml
	 * TODO:
	 *  <copyright-holder>
	 *  In Isabelle's mapping document:
	 *   Article order in the issue's Table of Contents
	 */
	function toXml(&$record, $format = null) {
		$article   =& $record->getData('article');
		$journal   =& $record->getData('journal');
		$section   =& $record->getData('section');
		$issue     =& $record->getData('issue');
		$galleys   =& $record->getData('galleys');
		$articleId = $article->getId();

		// Cache issue ordering information.
		static $issueId;
		static $sectionSeq;

		if (!isset($issueId) || $issueId != $issue->getId()) {
			$sectionDao = DAORegistry::getDAO('SectionDAO');
			$issueId = $issue->getId();
			$sections = $sectionDao->getByIssueId($issueId);
			$sectionSeq = array();
			$i=0;
			foreach ($sections as $thisSection) {
				$sectionSeq[$thisSection->getId()] = $i++;
			}
			unset($sections);
		}

		$abbreviation         = $journal->getLocalizedSetting('abbreviation');
		$printIssn            = $journal->getSetting('printIssn');
		$onlineIssn           = $journal->getSetting('onlineIssn');
		$primaryLocale        = $journal->getPrimaryLocale();
		$publisherInstitution = $journal->getSetting('publisherInstitution');
		$datePublished        = $article->getDatePublished();
		$primaryLocale2		  = strtoupper(substr($primaryLocale, 0, 2));

		if (!$datePublished) $datePublished = $issue->getDatePublished();
		if ($datePublished) $datePublished  = strtotime($datePublished);
		
		$response = "<journal\n" .
    				" xmlns='http://dialnet.unirioja.es'\n" .
    				" xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'\n" .
    				" xsi:schemaLocation='http://dialnet.unirioja.es DialnetArticle.xsd'>\n";
		
		//INFORMACION DE LA REVISTA
		$response .=
			(!empty($onlineIssn)?"\t<issn>" . htmlspecialchars(Core::cleanVar($onlineIssn)) . "</issn>":
				(!empty($printIssn)?"\t<issn>" . htmlspecialchars(Core::cleanVar($printIssn)) . "</issn>":'');
		$response .= "\n\t<title>" . htmlspecialchars(Core::cleanVar($journal->getLocalizedName())) . "</title>\n";


		//INFORMACION DEL EJEMPLAR
		$response .= "\t<issue>\n";
		$response .= "\t\t<year>" . htmlspecialchars(Core::cleanVar($issue->getYear())) . "</year>\n";
		$response .= ($issue->getShowVolume()?"\t\t<volume>" . htmlspecialchars(Core::cleanVar($issue->getVolume())) . "</volume>\n":'');
		$response .= ($issue->getShowNumber()?"\t\t<number>" . htmlspecialchars(Core::cleanVar($issue->getNumber())) . "</number>\n":'');
		$response .= ($issue->getShowTitle()?"\t\t<description>" . htmlspecialchars(Core::cleanVar($issue->getLocalizedTitle())) . "</description>\n":'');
		if ($issue->getLocalizedShowCoverPage()) {
		
			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$coverPagePath = $request->getBaseUrl() . '/';
			$coverPagePath .= $publicFileManager->getJournalFilesPath($journal->getId()) . '/';
		
			$response .= "\t<coverUrl>" . htmlspecialchars(Core::cleanVar(Request::url($coverPagePath, $issue->getLocalizedFileName()))) . "</coverUrl>\n";

		}
		$response .= "\t</issue>\n";

		$response .= "\t<article\n" .
					 "\t\txml:lang=\"" . $primaryLocale2 . "\">\n";



		$response = "<article\n" .
			(($s = $section->getLocalizedIdentifyType())!=''?"\tarticle-type=\"" . htmlspecialchars(Core::cleanVar($s)) . "\"":'');
			


		$response .=
			"\t\t<article-meta>\n" .
			"\t\t\t<article-id pub-id-type=\"other\">" . htmlspecialchars(Core::cleanVar($article->getBestArticleId())) . "</article-id>\n" .
			(($s = $article->getPubId('doi'))?"\t\t\t<article-id pub-id-type=\"doi\">" . htmlspecialchars(Core::cleanVar($s)) . "</article-id>\n":'') .
			"\t\t\t<article-categories><subj-group subj-group-type=\"heading\"><subject>" . htmlspecialchars(Core::cleanVar($section->getLocalizedTitle())) . "</subject></subj-group></article-categories>\n" .
			"\t\t\t<title-group>\n" .
			"\t\t\t\t<article-title>" . htmlspecialchars(Core::cleanVar(strip_tags($article->getLocalizedTitle()))) . "</article-title>\n";

		// Include translated article titles
		foreach ($article->getTitle(null) as $locale => $title) {
			if ($locale == $primaryLocale) continue;
			$response .= "\t\t\t\t<trans-title xml:lang=\"" . strtoupper(substr($locale, 0, 2)) . "\">" . htmlspecialchars(Core::cleanVar(strip_tags($title))) . "</trans-title>\n";
		}

		// AUTORES
		foreach ($article->getAuthors() as $author) {
			$response .=
				"\t\t\t\t<author>\n" .
				"\t\t\t\t\t\t<name>" . htmlspecialchars(Core::cleanVar($author->getFirstName()) . (($s = $author->getMiddleName()) != ''?" $s":'')) . "</name>\n" .
				"\t\t\t\t\t\t<sn>" . htmlspecialchars(Core::cleanVar($author->getLastName())) . "</sn>\n" .

				(($s = $author->getLocalizedAffiliation()) != ''?"\t\t\t\t\t<aff>" . htmlspecialchars(Core::cleanVar($s)) . "</aff>\n":'') .
				"\t\t\t\t\t<emailAddress>" . htmlspecialchars(Core::cleanVar($author->getEmail())) . "</emailAddress>\n" .
				(($s = $author->getUrl()) != ''?"\t\t\t\t\t<url>" . htmlspecialchars(Core::cleanVar($s)) . "</url>\n":'') .
				"\t\t\t\t</author>\n";
		}

		/*
		// FECHA DE PUBLICACION

		if ($datePublished) $response .=
			"\t\t\t<pub-date pub-type=\"epub\">\n" .
			"\t\t\t\t<day>" . strftime('%d', $datePublished) . "</day>\n" .
			"\t\t\t\t<month>" . strftime('%m', $datePublished) . "</month>\n" .
			"\t\t\t\t<year>" . strftime('%Y', $datePublished) . "</year>\n" .
			"\t\t\t</pub-date>\n";
		*/
		//TITULO


		//DOI


		// Incluir los numeros de pagina
		$matches = null;
		if (String::regexp_match_get('/^[Pp][Pp]?[.]?[ ]?(\d+)$/', $article->getPages(), $matches)) {
			$matchedPage = htmlspecialchars(Core::cleanVar($matches[1]));
			$response .= "\t\t\t\t<startPage>$matchedPage</startPage>\n";
		} elseif (String::regexp_match_get('/^[Pp][Pp]?[.]?[ ]?(\d+)[ ]?-[ ]?([Pp][Pp]?[.]?[ ]?)?(\d+)$/', $article->getPages(), $matches)) {
			$matchedPageFrom = htmlspecialchars(Core::cleanVar($matches[1]));
			$matchedPageTo = htmlspecialchars(Core::cleanVar($matches[3]));
			$response .=
				"\t\t\t\t<startPage>$matchedPageFrom</startPage>\n" .
				"\t\t\t\t<endPage>$matchedPageTo</endPage>\n";
		}

		//RESUMENES
		$abstract = htmlspecialchars(Core::cleanVar(strip_tags($article->getLocalizedAbstract())));
		if (!empty($abstract)) {
			$response .= "\t\t\t<abstract xml:lang=\"" . $primaryLocale2 . "\">$abstract</abstract>\n";
		}

		if (is_array($article->getAbstract(null))) foreach ($article->getAbstract(null) as $locale => $abstract) {
			//Si es el idioma principal o esta vacio no lo pintamos
			if ($locale == $primaryLocale || empty($abstract)) continue;
			$abstract = htmlspecialchars(Core::cleanVar(strip_tags($abstract)));

			if (empty($abstract)) continue;
			$response .= "\t\t\t<abstract xml:lang=\"" . $locale . "\">$abstract</abstract>\n";
		}

		//PALABRAS CLAVE

		$subjects = array();
		if (is_array($article->getSubject(null))) foreach ($article->getSubject(null) as $locale => $subject) {
			$s = array_map('trim', explode(';', Core::cleanVar($subject)));
			if (!empty($s)) $subjects[$locale] = $s;
		}
		if (!empty($subjects)) foreach ($subjects as $locale => $s) {
			$response .= "\t\t\t<keywords xml:lang=\"" . strtoupper(substr($locale, 0, 2)) . "\">\n";
			foreach ($s as $subject) $response .= "\t\t\t\t<keyword>" . htmlspecialchars($subject) . "</keyword>\n";
			$response .= "\t\t\t</keywords>\n";
		}

		$response .=
			"\t\t\t<permissions>\n" .
			((($s = $journal->getLocalizedSetting('copyrightNotice')) != '')?"\t\t\t\t<copyright-statement>" . htmlspecialchars(Core::cleanVar($s)) . "</copyright-statement>\n":'') .
			($datePublished?"\t\t\t\t<copyright-year>" . strftime('%Y', $datePublished) . "</copyright-year>\n":'') .
			"\t\t\t</permissions>\n";


		// TEXTOS COMPLETOS
		foreach ($article->getGalleys() as $galley) {
			$response .= "\t\t\t<fullTextUrl>" . htmlspecialchars(Core::cleanVar(Request::url($journal->getPath(), 'article', 'download', array($article->getBestArticleId(), $galley->getId())))) .
						 "</fullTextUrl>\n";
		}

	

		

	

		// Include body text (for search indexing only)
		import('classes.search.ArticleSearchIndex');
		$text = '';
		$galleys = $article->getGalleys();

		// Give precedence to HTML galleys, as they're quickest to parse
		usort($galleys, create_function('$a, $b', 'return $a->isHtmlGalley()?-1:1;'));

		// Determine any access limitations. If there are, do not
		// provide the full-text.
		import('classes.issue.IssueAction');
		$issueAction = new IssueAction();
		$subscriptionRequired = $issueAction->subscriptionRequired($issue);
		$isSubscribedDomain   = $issueAction->subscribedDomain($journal, $issue->getId(), $article->getId());

		if (!$subscriptionRequired || $isSubscribedDomain) foreach ($galleys as $galley) {
			$parser =& SearchFileParser::fromFile($galley);
			if ($parser && $parser->open()) {
				while(($s = $parser->read()) !== false) $text .= $s;
				$parser->close();
			}

			if ($galley->isHtmlGalley()) $text = strip_tags($text);
			unset($galley);
			// Use the first parseable galley.
			if (!empty($text)) break;
		}
		if (!empty($text)) $response .= "\t<body><p>" . htmlspecialchars(Core::cleanVar(Core::cleanVar($text))) . "</p></body>\n";

		// Add NLM citation info
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$nlmFilters = $filterDao->getObjectsByGroup('submission=>nlm23-article-xml');
		assert(count($nlmFilters) == 1);
		$nlmFilter = array_pop($nlmFilters);
		$nlmXmlDom = new DOMDocument();
		$nlmXmlDom->loadXML($nlmFilter->execute($article));
		$documentElement =& $nlmXmlDom->documentElement;

		// Work-around for hasChildNodes being stupid about whitespace.
		$hasChildren = false;
		if (isset($documentElement->childNodes)) foreach ($documentElement->childNodes as $c) {
			if ($c->nodeType == XML_ELEMENT_NODE) $hasChildren = true;
		}

		// If there were any citations, include them.
		if ($hasChildren) {
			$innerXml = $nlmXmlDom->saveXML($documentElement);
			$response .= "<back>$innerXml</back>\n";
		}

		$response .= "</article>";

		return $response;
	}

	function getEditorialInfo($journalId) {
		static $editorialInfo = array();
		if (isset($editorialInfo[$journalId])) return $editorialInfo[$journalId];

		$response = '';
		$roleDao = DAORegistry::getDAO('RoleDAO');
		$roleMap = array(ROLE_ID_EDITOR => 'editor', ROLE_ID_SECTION_EDITOR => 'secteditor', ROLE_ID_MANAGER => 'jmanager');
		foreach ($roleMap as $roleId => $roleName) {
			$users = $roleDao->getUsersByRoleId($roleId, $journalId);
			$isFirst = true;
			while ($user = $users->next()) {
				$response .= "\t\t\t\t<contrib contrib-type=\"$roleName\">\n" .
					"\t\t\t\t\t<name>\n" .
					"\t\t\t\t\t\t<surname>" . htmlspecialchars(Core::cleanVar($user->getLastName())) . "</surname>\n" .
					"\t\t\t\t\t\t<given-names>" . htmlspecialchars(Core::cleanVar($user->getFirstName() . ($user->getMiddleName() != ''?' ' . $user->getMiddleName():''))) . "</given-names>\n" .
					"\t\t\t\t\t</name>\n" .
					"\t\t\t\t</contrib>\n";
			}
		}
		$editorialInfo[$journalId] =& $response;
		return $response;
	}
}

?>
