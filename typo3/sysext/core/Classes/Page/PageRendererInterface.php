<?php
namespace TYPO3\CMS\Core\Page;

/**
 * Interface PageRendererInterface
 */
interface PageRendererInterface
{
    const PART_COMPLETE = 0;
    const PART_HEADER = 1;
    const PART_FOOTER = 2;
    const JQUERY_VERSION_LATEST = '3.2.1';
    const JQUERY_NAMESPACE_NONE = 'none';
    const JQUERY_NAMESPACE_DEFAULT = 'jQuery';
    const JQUERY_NAMESPACE_DEFAULT_NOCONFLICT = 'defaultNoConflict';

    /**
     * Sets the title
     *
     * @param string $title	title of webpage
     */
    public function setTitle($title);

    /**
     * Sets meta charset
     *
     * @param string $charSet Used charset
     */
    public function setCharSet($charSet);

    /**
     * Sets language
     *
     * @param string $lang Used language
     */
    public function setLanguage($lang);

    /**
     * Sets HTML base URL
     *
     * @param string $baseUrl HTML base URL
     */
    public function setBaseUrl($baseUrl);

    /**
     * Sets Content for Body
     *
     * @param string $content
     */
    public function setBodyContent($content);

    /**
     * Adds content to body content
     *
     * @param string $content
     */
    public function addBodyContent($content);

    /**
     * Sets xml prolog and docType
     *
     * @param string $xmlPrologAndDocType Complete tags for xml prolog and docType
     */
    public function setXmlPrologAndDocType($xmlPrologAndDocType);

    /**
     * Gets the title
     *
     * @return string $title Title of webpage
     */
    public function getTitle();

    /**
     * Gets the charSet
     *
     * @return string $charSet
     */
    public function getCharSet();

    /**
     * Gets the language
     *
     * @return string $lang
     */
    public function getLanguage();

    /**
     * Gets HTML base URL
     *
     * @return string $url
     */
    public function getBaseUrl();

    /**
     * Gets content for body
     *
     * @return string
     */
    public function getBodyContent();

    /**
     * @return string
     */
    public function getTemplateFile();

    /**
     * Adds meta data
     *
     * @param string $meta Meta data (complete metatag)
     */
    public function addMetaTag($meta);

    /**
     * Adds inline HTML comment
     *
     * @param string $comment
     */
    public function addInlineComment($comment);

    /**
     * Adds header data
     *
     * @param string $data Free header data for HTML header
     */
    public function addHeaderData($data);

    /**
     * Adds footer data
     *
     * @param string $data Free header data for HTML header
     */
    public function addFooterData($data);

    /**
     * Call this function if you need to include the jQuery library
     *
     * @param null|string $version The jQuery version that should be included, either "latest" or any available version
     * @param null|string $source The location of the jQuery source, can be "local", "google", "msn", "jquery" or just an URL to your jQuery lib
     * @param string $namespace The namespace in which the jQuery object of the specific version should be stored.
     * @throws \UnexpectedValueException
     */
    public function loadJquery($version = null, $source = null, $namespace = self::JQUERY_NAMESPACE_DEFAULT);

    /**
     * Render the section (Header or Footer)
     *
     * @param int $part Section which should be rendered: self::PART_COMPLETE, self::PART_HEADER or self::PART_FOOTER
     * @return string Content of rendered section
     */
    public function render($part = self::PART_COMPLETE);

    /**
     * Render the page but not the JavaScript and CSS Files
     *
     * @param string $substituteHash The hash that is used for the placehoder markers
     * @access private
     * @return string Content of rendered section
     */
    public function renderPageWithUncachedObjects($substituteHash);
}
