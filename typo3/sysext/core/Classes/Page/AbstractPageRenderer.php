<?php
namespace TYPO3\CMS\Core\Page;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractPageRenderer
 */
abstract class AbstractPageRenderer implements PageRendererInterface
{
    /**
     * @var string
     */
    protected $xmlPrologAndDocType = '';

    /**
     * @var array
     */
    protected $metaTags = [];

    /**
     * @var array
     */
    protected $inlineComments = [];

    /**
     * @var array
     */
    protected $headerData = [];

    /**
     * @var array
     */
    protected $footerData = [];

    /**
     * @var array
     */
    protected $jsInline = [];

    /**
     * @var array
     */
    protected $cssInline = [];

    /**
     * @var array
     */
    protected $cssFiles = [];

    /**
     * @var array
     */
    protected $jsFiles = [];

    /**
     * @var string
     */
    protected $bodyContent;

    /**
     * Charset for the rendering
     *
     * @var string
     */
    protected $charSet;

    /**
     * The title of the page
     *
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $jsFooterInline = [];

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $templateFile;

    /**
     * @var \TYPO3\CMS\Core\Localization\Locales
     */
    protected $locales;

    /**
     * The language key
     * Two character string or 'default'
     *
     * @var string
     */
    protected $lang;

    /**
     * List of language dependencies for actual language. This is used for local variants of a language
     * that depend on their "main" language, like Brazilian Portuguese or Canadian French.
     *
     * @var array
     */
    protected $languageDependencies = [];

    /**
     * The local directory where one can find jQuery versions and plugins
     *
     * @var string
     */
    protected $jQueryPath = 'EXT:core/Resources/Public/JavaScript/Contrib/jquery/';

    // Internal flags for JS-libraries
    /**
     * This array holds all jQuery versions that should be included in the
     * current page.
     * Each version is described by "source", "version" and "namespace"
     *
     * The namespace of every particular version is the key
     * of that array, because only one version per namespace can exist.
     *
     * The type "source" describes where the jQuery core should be included from
     * currently, TYPO3 supports "local" (make use of jQuery path), "google",
     * "jquery", "msn" and "cloudflare".
     *
     * Currently there are downsides to "local" which supports only the latest/shipped
     * jQuery core out of the box.
     *
     * @var array
     */
    protected $jQueryVersions = [];

    /**
     * Array of jQuery version numbers shipped with the core
     *
     * @var array
     */
    protected $availableLocalJqueryVersions = [
        self::JQUERY_VERSION_LATEST
    ];

    /**
     * Array of jQuery CDNs with placeholders
     *
     * @var array
     */
    protected $jQueryCdnUrls = [
        'google' => 'https://ajax.googleapis.com/ajax/libs/jquery/%1$s/jquery%2$s.js',
        'msn' => 'https://ajax.aspnetcdn.com/ajax/jQuery/jquery-%1$s%2$s.js',
        'jquery' => 'https://code.jquery.com/jquery-%1$s%2$s.js',
        'cloudflare' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/%1$s/jquery%2$s.js'
    ];

    /**
     * AbstractPageRenderer constructor.
     */
    public function __construct($templateFile = null)
    {
        $this->locales = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\Locales::class);
    }
    /**
     * Enables/disables rendering of XHTML code
     *
     * @param bool $enable Enable XHTML
     */
    public function setRenderXhtml($enable)
    {
        $this->renderXhtml = $enable;
    }

    /**
     * Sets the title
     *
     * @param string $title	title of webpage
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Sets meta charset
     *
     * @param string $charSet Used charset
     */
    public function setCharSet($charSet)
    {
        $this->charSet = $charSet;
    }

    /**
     * Sets language
     *
     * @param string $lang Used language
     */
    public function setLanguage($lang)
    {
        $this->lang = $lang;
        $this->languageDependencies = [];

        // Language is found. Configure it:
        if (in_array($this->lang, $this->locales->getLocales())) {
            $this->languageDependencies[] = $this->lang;
            foreach ($this->locales->getLocaleDependencies($this->lang) as $language) {
                $this->languageDependencies[] = $language;
            }
        }
    }

    /**
     * Sets HTML base URL
     *
     * @param string $baseUrl HTML base URL
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Sets Content for Body
     *
     * @param string $content
     */
    public function setBodyContent($content)
    {
        $this->bodyContent = $content;
    }

    /**
     * Adds content to body content
     *
     * @param string $content
     */
    public function addBodyContent($content)
    {
        $this->bodyContent .= $content;
    }

    /**
     * Gets the title
     *
     * @return string $title Title of webpage
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the charSet
     *
     * @return string $charSet
     */
    public function getCharSet()
    {
        return $this->charSet;
    }

    /**
     * Gets the language
     *
     * @return string $lang
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * Gets HTML base URL
     *
     * @return string $url
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Gets content for body
     *
     * @return string
     */
    public function getBodyContent()
    {
        return $this->bodyContent;
    }

    /**
     * Gets template file
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * Sets template file
     *
     * @param string $file
     */
    public function setTemplateFile($file)
    {
        $this->templateFile = $file;
    }

    /**
     * Sets xml prolog and docType
     *
     * @param string $xmlPrologAndDocType Complete tags for xml prolog and docType
     */
    public function setXmlPrologAndDocType($xmlPrologAndDocType)
    {
        $this->xmlPrologAndDocType = $xmlPrologAndDocType;
    }

    /**
     * Adds meta data
     *
     * @param string $meta Meta data (complete metatag)
     */
    public function addMetaTag($meta)
    {
        if (!in_array($meta, $this->metaTags)) {
            $this->metaTags[] = $meta;
        }
    }

    /**
     * Adds inline HTML comment
     *
     * @param string $comment
     */
    public function addInlineComment($comment)
    {
        if (!in_array($comment, $this->inlineComments)) {
            $this->inlineComments[] = $comment;
        }
    }

    /**
     * Adds header data
     *
     * @param string $data Free header data for HTML header
     */
    public function addHeaderData($data)
    {
        if (!in_array($data, $this->headerData)) {
            $this->headerData[] = $data;
        }
    }

    /**
     * Adds footer data
     *
     * @param string $data Free header data for HTML header
     */
    public function addFooterData($data)
    {
        if (!in_array($data, $this->footerData)) {
            $this->footerData[] = $data;
        }
    }

    /**
     * Call this function if you need to include the jQuery library
     *
     * @param null|string $version The jQuery version that should be included, either "latest" or any available version
     * @param null|string $source The location of the jQuery source, can be "local", "google", "msn", "jquery" or just an URL to your jQuery lib
     * @param string $namespace The namespace in which the jQuery object of the specific version should be stored.
     * @throws \UnexpectedValueException
     */
    public function loadJquery($version = null, $source = null, $namespace = self::JQUERY_NAMESPACE_DEFAULT)
    {
        // Set it to the version that is shipped with the TYPO3 core
        if ($version === null || $version === 'latest') {
            $version = self::JQUERY_VERSION_LATEST;
        }
        // Check if the source is set, otherwise set it to "default"
        if ($source === null) {
            $source = 'local';
        }
        if ($source === 'local' && !in_array($version, $this->availableLocalJqueryVersions)) {
            throw new \UnexpectedValueException('The requested jQuery version is not available in the local filesystem.', 1341505305);
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $namespace)) {
            throw new \UnexpectedValueException('The requested namespace contains non alphanumeric characters.', 1341571604);
        }
        $this->jQueryVersions[$namespace] = [
            'version' => $version,
            'source' => $source
        ];
    }

    /**
     * Reset all vars to initial values
     */
    protected function reset()
    {
        $this->cssInline = [];
        $this->metaTags = [];
        $this->jsInline = [];
        $this->jsFooterInline = [];
        $this->inlineComments = [];
        $this->headerData = [];
        $this->footerData = [];
        $this->cssFiles = [];
    }
}
