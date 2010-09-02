<?php
//******************************************************************************
// Class Dll
// http://expressionengine.com/docs/development/plugins.html 
//******************************************************************************

// EE Plugin Control Panel Information
$plugin_info = array(
    'pi_name' => 'PBS Digital Learning Library (DLL)',
    'pi_version' => '0.1',
    'pi_author' => "Jerry D'Antonio",
    'pi_author_url' => 'http://www.ideastream.org',
    'pi_description' => 'EE 1.6.x plugin for sending/parsing queries to the PBS DLL.',
    'pi_usage' => Dll::usage()
);

//------------------------------------------------------------------------------
// ExpressionEngine Setup
//------------------------------------------------------------------------------

// Load the XML parsing library
if ( ! class_exists('EE_XMLparser'))
{
    require PATH_CORE.'core.xmlparser'.EXT;
}

//------------------------------------------------------------------------------
// Dll Plugin Class
//------------------------------------------------------------------------------

/**
 * ExpressionEngine plugin for interfacing with the NPR API.
 **/
class Dll
{
    //--------------------------------------------------------------------------
    // ExpressionEngine Data Members
    //--------------------------------------------------------------------------

    public $return_data = '';

    // local pointers to ExpressionEngine global variables
    protected $_DB = NULL;
    protected $_DSP = NULL;
    protected $_FNS = NULL;
    protected $_IN = NULL;
    protected $_LANG = NULL;
    protected $_LOC = NULL;
    protected $_REGX = NULL;
    protected $_SESS = NULL;
    protected $_TMPL = NULL;

    //--------------------------------------------------------------------------
    // cURL Data Members
    //--------------------------------------------------------------------------

    // class constants
    const TICKET_SESSION_KEY = '__pbs_dll_api_ticket__';
    const CURL_TIMEOUT = 10;

    const AUTH_URL = 'https://dll-svc.pbs.org/alfresco/service/api/login?';
    const QUERY_URL = 'https://dll-svc.pbs.org/alfresco/service/stn/query?';
    const ASSET_URL = 'https://dll-svc.pbs.org/alfresco/service/stn/findAssetsById?';
    
    const ALL_ASSET_QUERY = 'e036cb86-2183-45ac-95e8-49d6acade6b8';
    const OPEN_TEXT_QUERY = '4ade20f4-883b-4943-aef7-596fd79a493f';
    const MEDIA_TYPE_QUERY = '5816536c-7a19-425d-afba-f66c8281b90a';
    const CURRICULUM_TOPIC_QUERY = 'ba32e4ce-5e84-4b8b-b4b0-14cb3ccaaaa7';

    // cURL handle
    private $_curl;

    // error variables
    private $_error;
    private $_errno;
    private $_response;
    private $_status;
    
    //--------------------------------------------------------------------------
    // Construction and Destruction
    //--------------------------------------------------------------------------
    
    /**
     * Constructor.
     **/
    function Dll()
    {
        $this->__construct();
    }

    /**
     * Constructor. Maps ExpressionEngine global variables to object
     * member variables. Also initializes the cURL library for network
     * communications.
     **/
    public function __construct()
    {
        session_start();

        global $DB, $DSP, $FNS, $IN, $LANG, $LOC, $REGX, $SESS, $TMPL;

        $this->_DB = $DB;
        $this->_DSP = $DSP;
        $this->_FNS = $FNS;
        $this->_IN = $IN;
        $this->_LANG = $LANG;
        $this->_LOC = $LOC;
        $this->_REGX = $REGX;
        $this->_SESS = $SESS;
        $this->_TMPL = $TMPL;

        $this->_curl = FALSE;
        $this->init();
    }
    
    /**
     * Destructor. Closes cURL connections and releases cURL library.
     **/
    public function __destruct()
    {
        if ($this->is_init()) curl_close($this->_curl);
    }

    //--------------------------------------------------------------------------
    // Operations
    //--------------------------------------------------------------------------

    /**
     * {exp:dll:asset user="<user>" pwd="<password>" id="<id 1>,<id 2>,<...>"}
     *     <h2>{title}</h2>
     *     <ul>
     *         {if edcarId}<li><b>EDCAR ID:</b> {edcarId}</li>{/if}
     *         {if type}<li><b>Type:</b> {type}</li>{/if}
     *         {if score}<li><b>Score:</b> {score}</li>{/if}
     *         {if accessibility}<li><b>Accessibility:</b> {accessibility}</li>{/if}
     *         {if aspectRatio}<li><b>Aspect Ratio:</b> {aspectRatio}</li>{/if}
     *         {if assetSubmitter}<li><b>Asset Submitter:</b> {assetSubmitter}</li>{/if}
     *         {if assetSubmitterTimestamp}<li><b>Asset Submitter Timestamp:</b> {assetSubmitterTimestamp}</li>{/if}
     *         {if colors}<li><b>Colors:</b> {colors}</li>{/if}
     *         {if contentFlagsDescription}<li><b>Content Flags Description:</b> {contentFlagsDescription}</li>{/if}
     *         {if contextualPageURL}<li><b>Contextual Page URL:</b> {contextualPageURL}</li>{/if}
     *         {if copyrightYear}<li><b>Copyright Year:</b> {copyrightYear}</li>{/if}
     *         {if dataRate}<li><b>Data Rate:</b> {dataRate}</li>{/if}
     *         {if dateAvailable}<li><b>Date Available:</b> {dateAvailable}</li>{/if}
     *         {if dateCreated}<li><b>Date Created:</b> {dateCreated}</li>{/if}
     *         {if dateIssued}<li><b>Date Issued:</b> {dateIssued}</li>{/if}
     *         {if description}<li><b>Description:</b> {description}</li>{/if}
     *         {if descriptionLong}<li><b>Description Long:</b> {descriptionLong}</li>{/if}
     *         {if descriptionShort}<li><b>Description Short:</b> {descriptionShort}</li>{/if}
     *         {if descriptionType}<li><b>Description Type:</b> {descriptionType}</li>{/if}
     *         {if downloadURL}<li><b>Download URL:</b> {downloadURL}</li>{/if}
     *         {if expirationDate}<li><b>Expiration Date:</b> {expirationDate}</li>{/if}
     *         {if extentDuration}<li><b>Extent Duration:</b> {extentDuration}</li>{/if}
     *         {if extentFileSize}<li><b>Extent File Size:</b> {extentFileSize}</li>{/if}
     *         {if externalId}<li><b>External Id:</b> {externalId}</li>{/if}
     *         {if externalIdSource}<li><b>External Id Source:</b> {externalIdSource}</li>{/if}
     *         {if formatDigital}<li><b>Format Digital:</b> {formatDigital}</li>{/if}
     *         {if frameRate}<li><b>Frame Rate:</b> {frameRate}</li>{/if}
     *         {if geographicAccessRestrictionType}<li><b>Geographic Access Restriction Type:</b> {geographicAccessRestrictionType}</li>{/if}
     *         {if languagePrimary}<li><b>Language Primary:</b> {languagePrimary}</li>{/if}
     *         {if mediaTypeGeneral}<li><b>Media Type General:</b> {mediaTypeGeneral}</li>{/if}
     *         {if mediaTypeSpecific}<li><b>Media Type Specific:</b> {mediaTypeSpecific}</li>{/if}
     *         {if pathToMediaItem}<li><b>Path To Media Item:</b> {pathToMediaItem}</li>{/if}
     *         {if renderingWindowHeight}<li><b>Rendering Window Height:</b> {renderingWindowHeight}</li>{/if}
     *         {if renderingWindowWidth}<li><b>Rendering Window Width:</b> {renderingWindowWidth}</li>{/if}
     *         {if rights}<li><b>Rights:</b> {rights}</li>{/if}
     *         {if rightsDistribution}<li><b>Rights Distribution:</b> {rightsDistribution}</li>{/if}
     *         {if rightsSummary}<li><b>Rights Summary:</b> {rightsSummary}</li>{/if}
     *         {if rightsSummaryModifyType}<li><b>Rights Summary Modify Type:</b> {rightsSummaryModifyType}</li>{/if}
     *         {if samplingRate}<li><b>Sampling Rate:</b> {samplingRate}</li>{/if}
     *         {if tier}<li><b>Tier:</b> {tier}</li>{/if}
     *         {if timeStart}<li><b>Time Start:</b> {timeStart}</li>{/if}
     *         {if title}<li><b>Title:</b> {title}</li>{/if}
     *         {if vendedBy}<li><b>Vended By:</b> {vendedBy}</li>{/if}
     *         {if audienceLevels}<li><b>Audience Levels:</b> {audienceLevels backspace="2"}{audienceLevel}, {/audienceLevels}</li>{/if}
     *         {if contentFlags}<li><b>Content Flags:</b> {contentFlags backspace="2"}{contentFlag}, {/contentFlags}</li>{/if}
     *         {if contributors}<li><b>Contributors:</b> {contributors backspace="2"}{contributor}, {/contributors}</li>{/if}
     *         {if contributorTypes}<li><b>Contributor Types:</b> {contributorTypes backspace="2"}{contributorType}, {/contributorTypes}</li>{/if}
     *         {if coverageEvents}<li><b>Coverage Events:</b> {coverageEvents backspace="2"}{coverageEvent}, {/coverageEvents}</li>{/if}
     *         {if coverageEventContributors}<li><b>Coverage Event Contributors:</b> {coverageEventContributors backspace="2"}{coverageEventContributor}, {/coverageEventContributors}</li>{/if}
     *         {if coverageGeographicals}<li><b>Coverage Geographicals:</b> {coverageGeographicals backspace="2"}{coverageGeographical}, {/coverageGeographicals}</li>{/if}
     *         {if coverageGeographicalContributors}<li><b>Coverage Geographical Contributors:</b> {coverageGeographicalContributors backspace="2"}{coverageGeographicalContributor}, {/coverageGeographicalContributors}</li>{/if}
     *         {if coverageOrganizations}<li><b>Coverage Organizations:</b> {coverageOrganizations backspace="2"}{coverageOrganization}, {/coverageOrganizations}</li>{/if}
     *         {if coverageOrganizationContributors}<li><b>Coverage Organization Contributors:</b> {coverageOrganizationContributors backspace="2"}{coverageOrganizationContributor}, {/coverageOrganizationContributors}</li>{/if}
     *         {if coveragePeoples}<li><b>Coverage Peoples:</b> {coveragePeoples backspace="2"}{coveragePeople}, {/coveragePeoples}</li>{/if}
     *         {if coveragePeopleContributors}<li><b>Coverage People Contributors:</b> {coveragePeopleContributors backspace="2"}{coveragePeopleContributor}, {/coveragePeopleContributors}</li>{/if}
     *         {if coverageTimePeriods}<li><b>Coverage Time Periods:</b> {coverageTimePeriods backspace="2"}{coverageTimePeriod}, {/coverageTimePeriods}</li>{/if}
     *         {if coverageTimePeriodContributors}<li><b>Coverage Time Period Contributors:</b> {coverageTimePeriodContributors backspace="2"}{coverageTimePeriodContributor}, {/coverageTimePeriodContributors}</li>{/if}
     *         {if creators}<li><b>Creators:</b> {creators backspace="2"}{creator}, {/creators}</li>{/if}
     *         {if creatorOthers}<li><b>Creator Others:</b> {creatorOthers backspace="2"}{creatorOther}, {/creatorOthers}</li>{/if}
     *         {if creatorOtherTypes}<li><b>Creator Other Types:</b> {creatorOtherTypes backspace="2"}{creatorOtherType}, {/creatorOtherTypes}</li>{/if}
     *         {if creatorTypes}<li><b>Creator Types:</b> {creatorTypes backspace="2"}{creatorType}, {/creatorTypes}</li>{/if}
     *         {if curriculumGeneralTopics}<li><b>Curriculum General Topics:</b> {curriculumGeneralTopics backspace="2"}{curriculumGeneralTopic}, {/curriculumGeneralTopics}</li>{/if}
     *         {if curriculumStandards}<li><b>Curriculum Standards:</b> {curriculumStandards backspace="2"}{curriculumStandard}, {/curriculumStandards}</li>{/if}
     *         {if encodings}<li><b>Encodings:</b> {encodings backspace="2"}{encoding}, {/encodings}</li>{/if}
     *         {if geographicAccessRestrictions}<li><b>Geographic Access Restrictions:</b> {geographicAccessRestrictions backspace="2"}{geographicAccessRestriction}, {/geographicAccessRestrictions}</li>{/if}
     *         {if gradeLevels}<li><b>Grade Levels:</b> {gradeLevels backspace="2"}{gradeLevel}, {/gradeLevels}</li>{/if}
     *         {if keywords}<li><b>Keywords:</b> {keywords backspace="2"}{keyword}, {/keywords}</li>{/if}
     *         {if keywordsContributors}<li><b>Keywords Contributors:</b> {keywordsContributors backspace="2"}{keywordsContributor}, {/keywordsContributors}</li>{/if}
     *         {if languageAlternates}<li><b>Language Alternates:</b> {languageAlternates backspace="2"}{languageAlternate}, {/languageAlternates}</li>{/if}
     *         {if learningObjectives}<li><b>Learning Objectives:</b> {learningObjectives backspace="2"}{learningObjective}, {/learningObjectives}</li>{/if}
     *         {if titleAlternatives}<li><b>Title Alternatives:</b> {titleAlternatives backspace="2"}{titleAlternative}, {/titleAlternatives}</li>{/if}
     *         {if titleTypes}<li><b>Title Types:</b> {titleTypes backspace="2"}{titleType}, {/titleTypes}</li>{/if}
     *     </ul>
     * 
     *     {if taxonomys}<p><b>Taxonomies:</b></p>
     *         <ul>
     *             {taxonomys}<li>{taxonomy}</li>{/taxonomys}
     *         </ul>
     *     {/if}
     * 
     *     {if thumbnails}
     *         <p><b>Thumbnails:</b></p>
     *         {thumbnails}
     *         <ul>
     *             {if url}<li><b>URL:</b> {url}</li>{/if}
     *             {if renderingWindowWidth}<li><b>Rendering Window Width:</b> {renderingWindowWidth}</li>{/if}
     *             {if renderingWindowHeight}<li><b>Rendering Window Height:</b> {renderingWindowHeight}</li>{/if}
     *         </ul>
     *         {/thumbnails}
     *     {/if}
     * 
     *     {if transcript}
     *         <p><b>Transcript:</b></p>
     *         {transcript}
     *             <ul>
     *                 {if url}<li><b>URL:</b> {url}</li>{/if}
     *                 {if name}<li><b>Name:</b> {name}</li>{/if}
     *                 {if description}<li><b>Description:</b> {description}</li>{/if}
     *             </ul>
     *         {/transcript}
     *     {/if}
     * 
     *     {if renditions}
     *         <p><b>Renditions:</b></p>
     *         {renditions}
     *         <ul>
     *             {if renditionType}<li><b>Rendition Type:</b> {renditionType}</li>{/if}
     *             {if deliveryProtocols}<li><b>Delivery Protocols:</b> {deliveryProtocols}</li>{/if}
     *             {if urlHttp}<li><b>HTTP URL:</b> {urlHttp}</li>{/if}
     *             {if urlMMS}<li><b>MMS URL:</b> {urlMMS}</li>{/if}
     *             {if urlRtmp}<li><b>RTMP URL:</b> {urlRtmp}</li>{/if}
     *             {if timeStart}<li><b>Start Time:</b> {timeStart}</li>{/if}
     *             {if extentDuration}<li><b>Extent Duration:</b> {extentDuration}</li>{/if}
     *             {if extentFileSize}<li><b>Extent File Size:</b> {extentFileSize}</li>{/if}
     *             {if samplingRate}<li><b>Sampling Rate:</b> {samplingRate}</li>{/if}
     *             {if aspectRatio}<li><b>Aspect Ratio:</b> {aspectRatio}</li>{/if}
     *             {if frameRate}<li><b>Frame Rate:</b> {frameRate}</li>{/if}
     *             {if colors}<li><b>Colors:</b> {colors}</li>{/if}
     *             {if renderingWindowHeight}<li><b>Rendering Window Height:</b> {renderingWindowHeight}</li>{/if}
     *             {if renderingWindowWidth}<li><b>Rendering Window Width:</b> {renderingWindowWidth}</li>{/if}
     *         </ul>
     *         {/renditions}
     *     {/if}
     * 
     * {/exp:dll:asset}
     **/
    public function asset()
    {
        $tagdata = '';
        $assets = $this->_TMPL->fetch_param('id');

        if ($assets)
        {
            // build the query
            $url = self::ASSET_URL;
            $assets = explode(',', $assets);
            foreach ($assets as $asset) {
                $url .= '&id[]=' . trim($asset);
            }

            // send the request
            $tagdata = $this->send_dll_request($url);
        }

        return $tagdata;
    }

    public function all_assets()
    {
        $tagdata = '';

        $ticket = $this->request_ticket();
        if ($ticket)
        {
            // send the request
            $url = self::QUERY_URL . 'queryId=' . self::ALL_ASSET_QUERY;
            $tagdata = $this->send_dll_request($url);
        }

        return $tagdata;
    }

    //--------------------------------------------------------------------------
    // Template Utilities
    //--------------------------------------------------------------------------

    protected function process_asset_tagdata(/*XML_Cache Object*/ $asset, /*string*/ $tagdata) /*string*/
    {
        $cond = array();
        $id = $cond['edcarId'] = $asset->attributes['edcarId'];
        $cond['type'] = $asset->attributes['type'];
        $cond['score'] = $asset->attributes['score'];

        $tagdata = $this->_FNS->prep_conditionals($tagdata, $cond);

        foreach ($cond as $key => $value)
        {
            $tagdata = $this->_TMPL->swap_var_single($key, $value, $tagdata);
        }

        foreach ($asset->children as $element)
        {
            switch ($element->tag)
            {
            case 'properties':
                $tagdata = $this->process_asset_pair_properties($element->children, $tagdata, $id);
                $tagdata = $this->process_asset_single_properties($element->children, $tagdata, $id);
                break;
            case 'taxonomys':
                $tagdata = $this->process_asset_taxonomys($element->children, $tagdata, $id);
                break;
            case 'thumbnails':
                $tagdata = $this->process_asset_thumbnails($element->children, $tagdata, $id);
                break;
            case 'closedCaptions':
                $tagdata = $this->process_asset_closed_captions($element->children, $tagdata, $id);
                break;
            case 'transcript':
                $tagdata = $this->process_asset_transcript($element->children, $tagdata, $id);
                break;
            case 'orderedAssets':
                $tagdata = $this->process_asset_ordered_assets($element->children, $tagdata, $id);
                break;
            case 'unorderedAssets':
                $tagdata = $this->process_asset_unordered_assets($element->children, $tagdata, $id);
                break;
            case 'renditions':
                $tagdata = $this->process_asset_renditions($element->children, $tagdata, $id);
                break;
            }
        }

        $tagdata = $this->process_remaining_conditionals($tagdata);

        return $tagdata;
    }

    protected function process_asset_single_properties(/*array*/ $props, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        // conditional buffer
        $cond = array();

        // process properties
        foreach ($props as $prop)
        {
            if (! is_array($prop->children))
            {
                $cond[$prop->tag] = $prop->value;
                $tagdata = $this->_TMPL->swap_var_single($prop->tag, $prop->value, $tagdata);
            }
        }

        // process conditional tags
        $tagdata = $this->_FNS->prep_conditionals($tagdata, $cond);

        return $tagdata;
    }

    protected function process_asset_pair_properties(/*array*/ $props, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        // conditional buffer
        $cond = array();

        foreach ($props as $prop)
        {
            if (is_array($prop->children))
            {
                // get tag name
                $tag = $prop->tag;

                // add the tag to the conditional array
                $cond[$tag] = TRUE;

                // extract the full tag data for this tag
                $pattern = '/('.LD.$tag.'(\s+backspace="(\d+)")?'.RD.')(.*?)('.LD.SLASH.$tag.RD.')/s';
                $count = preg_match_all($pattern, $tagdata, $matches, PREG_SET_ORDER);

                // process all matches
                foreach ($matches as $match)
                {
                    $tdata = '';
                    foreach ($prop->children as $child)
                    {
                        $pattern = '/'.LD.$child->tag.RD.'/';
                        $tdata .= preg_replace($pattern, $child->value, $match[4]);
                    }

                    // replace the full tag
                    $tdata = substr($tdata, 0, strlen($tdata)-intval($match[3]));
                    $tagdata = str_replace($match[0], $tdata, $tagdata);
                }
            }
        }

        // process conditional tags
        $tagdata = $this->_FNS->prep_conditionals($tagdata, $cond);

        return $tagdata;
    }

    protected function process_asset_taxonomys(/*array*/ $taxonomys, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        // conditional buffer
        $cond = array();

        if (is_array($taxonomys))
        {
            $tag = 'taxonomys';

            // extract the full tag data for this tag
            $pattern = '/('.LD.$tag.'(\s+backspace="(\d+)")?'.RD.')(.*?)('.LD.SLASH.$tag.RD.')/s';
            $count = preg_match_all($pattern, $tagdata, $matches, PREG_SET_ORDER);

            // process all matches
            foreach ($matches as $match)
            {
                $tdata = '';
                foreach ($taxonomys as $child)
                {
                    $pattern = '/'.LD.$child->tag.RD.'/';
                    $tdata .= preg_replace($pattern, $child->value, $match[4]);
                }

                // replace the full tag
                $tdata = substr($tdata, 0, strlen($tdata)-intval($match[3]));
                $tagdata = str_replace($match[0], $tdata, $tagdata);
            }

            $tagdata = $this->_FNS->prep_conditionals($tagdata, array($tag => TRUE));
        }
        else
        {
            $tagdata = $this->_FNS->prep_conditionals($tagdata, array($tag => FALSE));
        }

        return $tagdata;
    }

    protected function process_asset_thumbnails(/*array*/ $thumbnails, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        $tagdata = $this->_FNS->prep_conditionals($tagdata, array('thumbnails' => TRUE));

        foreach ($thumbnails as $thumbnail)
        {
            $cond = array(
                'url' => FALSE,
                'renderingWindowWidth' => FALSE,
                'renderingWindowHeight' => FALSE,
            );

            foreach ($thumbnail->children as $attr)
            {
                $cond[$attr->tag] = $attr->value;
            }

            // extract the full tag data for this tag
            $tag = 'thumbnails';
            $pattern = '/('.LD.$tag.'(\s+backspace="(\d+)")?'.RD.')(.*?)('.LD.SLASH.$tag.RD.')/s';
            $count = preg_match_all($pattern, $tagdata, $matches, PREG_SET_ORDER);

            // process all matches
            foreach ($matches as $match)
            {
                $tdata = $match[4];
                $tdata = $this->_FNS->prep_conditionals($tdata, $cond);
                foreach ($cond as $key => $value)
                {
                    $pattern = '/'.LD.$key.RD.'/';
                    $tdata = preg_replace($pattern, $value, $tdata);
                }

                // replace the full tag
                $tdata = substr($tdata, 0, strlen($tdata)-intval($match[3]));
                $tagdata = str_replace($match[0], $tdata, $tagdata);
            }
        }

        return $tagdata;
    }

    protected function process_asset_closed_captions(/*array*/ $captions, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        echo "<p>process_asset_closed_captions for {$id}</p>";
        return $tagdata;
    }

    protected function process_asset_transcript(/*array*/ $transcript, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        $tagdata = $this->_FNS->prep_conditionals($tagdata, array('transcript' => TRUE));

        $cond = array(
            'url' => FALSE,
            'name' => FALSE,
            'description' => FALSE,
        );

        foreach ($transcript as $attr)
        {
            switch ($attr->tag)
            {
            case 'url':
                $cond['url'] = $attr->value;
                break;
            case 'name':
                $cond['name'] = $attr->value;
                break;
            case 'description':
                $cond['description'] = $attr->value;
                break;
            }
        }

        // extract the full tag data for this tag
        $tag = 'transcript';
        $pattern = '/('.LD.$tag.'(\s+backspace="(\d+)")?'.RD.')(.*?)('.LD.SLASH.$tag.RD.')/s';
        $count = preg_match_all($pattern, $tagdata, $matches, PREG_SET_ORDER);

        // process all matches
        foreach ($matches as $match)
        {
            $tdata = $match[4];
            $tdata = $this->_FNS->prep_conditionals($tdata, $cond);
            foreach ($cond as $key => $value)
            {
                $pattern = '/'.LD.$key.RD.'/';
                $tdata = preg_replace($pattern, $value, $tdata);
            }

            // replace the full tag
            $tdata = substr($tdata, 0, strlen($tdata)-intval($match[3]));
            $tagdata = str_replace($match[0], $tdata, $tagdata);
        }

        return $tagdata;
    }

    protected function process_asset_ordered_assets(/*array*/ $assets, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        echo "<p>process_asset_ordered_assets for {$id}</p>";
        return $tagdata;
    }

    protected function process_asset_unordered_assets(/*array*/ $assets, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        echo "<p>process_asset_unordered_assets for {$id}</p>";
        return $tagdata;
    }

    protected function process_asset_renditions(/*array*/ $renditions, /*string*/ $tagdata, /*string*/ $id) /*string*/
    {
        $tagdata = $this->_FNS->prep_conditionals($tagdata, array('renditions' => TRUE));

        foreach ($renditions as $rendition)
        {
            $cond = array(
				'renditionType' => FALSE,
				'deliveryProtocols' => FALSE,
				'urlHttp' => FALSE,
				'urlMMS' => FALSE,
				'urlRtmp' => FALSE,
				'timeStart' => FALSE,
				'extentDuration' => FALSE,
				'extentFileSize' => FALSE,
				'samplingRate' => FALSE,
				'aspectRatio' => FALSE,
				'frameRate' => FALSE,
				'colors' => FALSE,
				'renderingWindowHeight' => FALSE,
				'renderingWindowWidth' => FALSE,
            );

            foreach ($rendition->children as $attr)
            {
                if ($attr->tag == 'deliveryProtocols')
                {
                    $cond['deliveryProtocols'] = '';
                    foreach ($attr->children as $protocol) {
                        $cond['deliveryProtocols'] .= $protocol->value . ', ';
                    }
                    $cond['deliveryProtocols'] =
                        substr($cond['deliveryProtocols'], 0, strlen($cond['deliveryProtocols'])-2);
                }
                else
                {
                    $cond[$attr->tag] = $attr->value;
                }
            }

            // extract the full tag data for this tag
            $tag = 'renditions';
            $pattern = '/('.LD.$tag.'(\s+backspace="(\d+)")?'.RD.')(.*?)('.LD.SLASH.$tag.RD.')/s';
            $count = preg_match_all($pattern, $tagdata, $matches, PREG_SET_ORDER);

            // process all matches
            foreach ($matches as $match)
            {
                $tdata = $match[4];
                $tdata = $this->_FNS->prep_conditionals($tdata, $cond);
                foreach ($cond as $key => $value)
                {
                    $pattern = '/'.LD.$key.RD.'/';
                    $tdata = preg_replace($pattern, $value, $tdata);
                }

                // replace the full tag
                $tdata = substr($tdata, 0, strlen($tdata)-intval($match[3]));
                $tagdata = str_replace($match[0], $tdata, $tagdata);
            }
        }

        return $tagdata;
    }

    protected function process_remaining_conditionals(/*string*/ $tagdata) /*string*/
    {
        $cond = array();

        foreach ($this->_TMPL->var_single as $var)
        {
            if (! array_key_exists($var, $cond)) {
                $cond[$var] = FALSE;
            }
        }
        
        foreach ($this->_TMPL->var_pair as $var => $opts)
        {
            preg_match('/^\S+/', $var, $matches);
            if (! array_key_exists($matches[0], $cond)) {
                $cond[$matches[0]] = FALSE;
            }
        }

        $tagdata = $this->_FNS->prep_conditionals($tagdata, $cond);

        return $tagdata;
    }

    //--------------------------------------------------------------------------
    // Messaging Utilities
    //--------------------------------------------------------------------------

    protected function ticket(/*string*/ $ticket = false) /*mixed*/
    {
        if ($ticket) {
            $_SESSION[self::TICKET_SESSION_KEY] = $ticket;
        } else if (isset($_SESSION[self::TICKET_SESSION_KEY])) {
            $ticket = $_SESSION[self::TICKET_SESSION_KEY];
        } else {
            $ticket = false;
        }

        return $ticket;
    }

	/**
	 * <ticket>TICKET_ebbf06d2ec23442f470e719c2074c308bb352512</ticket>
	 **/
    protected function request_ticket(/*string*/ $user = false, /*string*/ $pwd = false)
	{
		// set the ticket
		$ticket = $this->ticket();

        if (! $ticket)
        {
            // get the user information
            if (! $user) $user = $this->_TMPL->fetch_param('user');
            if (! $pwd) $pwd = $this->_TMPL->fetch_param('pwd');
            if (! $pwd) $pwd = $this->_TMPL->fetch_param('password');

            // send the request
            $url = self::AUTH_URL . "u={$user}&pw={$pwd}";
            $this->setopt(CURLOPT_HTTPGET, TRUE);
            $this->setopt(CURLOPT_URL, $url);
            $ok = $this->exec();

            // process the response
            if ($ok)
            {
				$XML = new EE_XMLparser;
				$xml_obj = $XML->parse_xml($this->_response);
                if (isset($xml_obj->value))
                {
                    $ticket = $xml_obj->value;
                    $this->ticket($ticket);
                }
            }
        }

        return $ticket;
	}

    protected function send_dll_request(/*string*/ $url) /*string*/
    {
        $tagdata = '';

        // get the api ticket
        $ticket = $this->request_ticket();
        if ($ticket)
        {
            // send the request
            $url .= '&alf_ticket=' . $ticket;
            $this->setopt(CURLOPT_HTTPGET, TRUE);
            $this->setopt(CURLOPT_URL, $url);
            $ok = $this->exec();

            if ($ok)
            {
                // parse the result xml
                $XML = new EE_XMLparser;
                $result = $XML->parse_xml($this->_response);

                // determine the limit
                $limit = intval($this->_TMPL->fetch_param('limit'));
                if ($limit === 0) {
                    $limit = count($result->children);
                } else {
                    $limit = min($limit, count($result->children));
                }

                // loop through and process the results
                for ($i = 0; $i < $limit; $i++) {
                    $tagdata .= $this->process_asset_tagdata($result->children[$i], $this->_TMPL->tagdata);
                }
            }
        }

        return $tagdata;
    }
     
    //---------------------------------------------------------------------------
    // cURL Operations
    //---------------------------------------------------------------------------
    
    /**
     * Set the internal data members to valued representing a bad handle error.
     **/
    private function set_bad_handle() /*void*/ {
        $this->_response = '';
        $this->_errno = CURLM_BAD_HANDLE;
        $this->_error = 'The passed-in handle is not a valid cURL handle.';
        $this->_status = 0;
    }

    /**
     * Has the cURL library been correctly initialized?
     *
     * @return True if initialized else false.
     **/
    public function is_init() /*bool*/ {
        return $this->_curl !== FALSE;
    }

    /**
     * Close the current cURL handle and initialize the object anew.
     *
     * @see close
     * @see init
     *
     * @return True if initialization is successful else false.
     **/
    private function reset() /*bool*/ {
        $this->close();
        return $this->init();
    }
    
    /**
     * Initilize the internal cURL handle. Will not do anything if the object
     * has already been initialized.
     *
     * @return True if initialization is successful else false.
     **/
    private function init() /*bool*/ {
    
        if (! $this->is_init()) {
    
            // initialize a new session
            $this->_curl = curl_init();
    
            // set common options on success
            if ($this->_curl !== FALSE) {
                curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT);
            }
            else
            {
                $this->_response = '';
                $this->_errno = CURLE_FAILED_INIT;
                $this->_error = 'Failed to initialize the cURL session.';
                $this->_status = 0;
            }
        }
    
        // return a boolean
        return $this->is_init();
    }
    
    /**
     * Close the internal cURL handle and "zero" all internal data values.
     **/
    private function close() /*void*/ {
    
        // close an open session if it exists
        if ($this->is_init()) {
            curl_close($this->_curl);
            $this->_curl = FALSE;
        }
    
        // reset the error variables
        $this->_error = '';
        $this->_errno = CURLE_OK;
        $this->_response = '';
        $this->_status = 0;
    }
    
    /**
     * Set a cURL option.
     *
     * @link http://www.php.net/manual/en/function.curl-setopt.php 
     *
     * @param $option The defined constant for the option being set.
     * @param $value The value the option is to be set to.
     *
     * @return TRUE if successful else FALSE.
     **/
    private function setopt(/*int*/ $option, /*mixed*/ $value) /*BOOL*/ {
        if ($this->_curl) {
            return curl_setopt($this->_curl, (int)$option, $value);
        } else {
            return FALSE;
        }
    }
    
    /**
     * Convenience override of the underlying curl_getinfo method.
     *
     * @link http://www.php.net/manual/en/function.curl-getinfo.php 
     *
     * @param $opt The specific option to be retrieved or null for all options.
     *
     * @return A string when $opt is not null else an associative array.
     **/
    private function getinfo(/*int*/ $opt = null) /*mixes*/ {
        if (! $this->is_init()) {
            return null;
        } else if ($opt == null) {
            return curl_getinfo($this->_curl);
        } else {
            return curl_getinfo($this->_curl, $opt);
        }
    }
    
    /**
     * Execute the cURL command with the given parameters.
     *
     * @return true on success else false.
     **/
    private function exec() /*bool*/ {
        if ($this->_curl) {
            $this->_response = curl_exec($this->_curl);
            $this->_errno = curl_errno($this->_curl);
            $this->_error = curl_error($this->_curl);
            $this->_status = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        } else {
            $this->set_bad_handle();
        }
        return $this->_errno == CURLE_OK;
    }
   
    //--------------------------------------------------------------------------
    // Plugin Usage
    //--------------------------------------------------------------------------
    
    function usage()
    {
        ob_start(); 
?>
<?php
        $buffer = ob_get_contents();        
        ob_end_clean(); 
        return $buffer;
    }
    // END
}
?>
<?php
//******************************************************************************
// @author Jerry D'Antonio
// @see http://www.ideastream.org
// @copyright Copyright (c) ideastream
// @license http://www.opensource.org/licenses/mit-license.php
//******************************************************************************
// Copyright (c) ideastream
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//******************************************************************************
?>
