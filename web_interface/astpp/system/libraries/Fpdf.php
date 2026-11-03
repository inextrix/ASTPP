<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.7                                                                 *
* Date:    2011-06-18                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

if ( ! class_exists('FPDF'))
{
define('FPDF_VERSION', '1.7');

#[\AllowDynamicProperties]
class FPDF
{
var $page; // current page number
var $n; // current object number
var $offsets; // array of object offsets
var $buffer; // buffer holding in-memory PDF
var $pages; // array containing pages
var $state; // current document state
var $compress; // compression flag
var $k; // scale factor (number of points in user unit)
var $DefOrientation; // default orientation
var $CurOrientation; // current orientation
var $StdPageSizes; // standard page sizes
var $DefPageSize; // default page size
var $CurPageSize; // current page size
var $PageSizes; // used for pages with non default sizes or orientations
var $wPt, $hPt; // dimensions of current page in points
var $w, $h; // dimensions of current page in user unit
var $lMargin; // left margin
var $tMargin; // top margin
var $rMargin; // right margin
var $bMargin; // page break margin
var $cMargin; // cell margin
var $x, $y; // current position in user unit
var $lasth; // height of last printed cell
var $LineWidth; // line width in user unit
var $fontpath; // path containing fonts
var $CoreFonts; // array of core font names
var $fonts; // array of used fonts
var $FontFiles; // array of font files
var $diffs; // array of encoding differences
var $FontFamily; // current font family
var $FontStyle; // current font style
var $underline; // underlining flag
var $CurrentFont; // current font info
var $FontSizePt; // current font size in points
var $FontSize; // current font size in user unit
var $DrawColor; // commands for drawing color
var $FillColor; // commands for filling color
var $TextColor; // commands for text color
var $ColorFlag; // indicates whether fill and text colors are different
var $ws; // word spacing
var $images; // array of used images
var $PageLinks; // array of links in pages
var $links; // array of internal links
var $AutoPageBreak; // automatic page breaking
var $PageBreakTrigger; // threshold used to trigger page breaks
var $InHeader; // flag set when processing header
var $InFooter; // flag set when processing footer
var $ZoomMode; // zoom display mode
var $LayoutMode; // layout display mode
var $title; // title
var $subject; // subject
var $author; // author
var $keywords; // keywords
var $creator; // creator
var $AliasNbPages; // alias for total number of pages
var $PDFVersion; // PDF version number

/*******************************************************************************
*                                                                              *
*                               Public methods                                 *
*                                                                              *
*******************************************************************************/
function __construct($props = array())
{
	 if(astpp_count($props) == 3)
	 {
		 list($orientation,$unit,$format) = $props;
		 $this->initialize($orientation,$unit,$format);
	 }
}

function initialize($orientation='P',$unit='mm',$size='A4')
{
	// Some checks
	$this->_dochecks();
	// Initialization of properties
	$this->page = 0;
	$this->n = 2;
	$this->buffer = '';
	$this->pages = array();
	$this->PageSizes = array();
	$this->state = 0;
	$this->fonts = array();
	$this->FontFiles = array();
	$this->diffs = array();
	$this->images = array();
	$this->links = array();
	$this->InHeader = false;
	$this->InFooter = false;
	$this->lasth = 0;
	$this->FontFamily = '';
	$this->FontStyle = '';
	$this->FontSizePt = 12;
	$this->underline = false;
	$this->DrawColor = '0 G';
	$this->FillColor = '0 g';
	$this->TextColor = '0 g';
	$this->ColorFlag = false;
	$this->ws = 0;
	// Font path
	if (defined('FPDF_FONTPATH'))
	{
		$this->fontpath = FPDF_FONTPATH;
		if (substr($this->fontpath, -1) != '/' && substr($this->fontpath, -1) != '\\')
			$this->fontpath .= '/';
	}
	elseif (is_dir(dirname(__FILE__).'/font'))
		$this->fontpath = dirname(__FILE__).'/font/';
	else
		$this->fontpath = '';
	// Core fonts
	$this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
	// Scale factor
	if ($unit == 'pt')
		$this->k = 1;
	elseif ($unit == 'mm')
		$this->k = 72 / 25.4;
	elseif ($unit == 'cm')
		$this->k = 72 / 2.54;
	elseif ($unit == 'in')
		$this->k = 72;
	else
		$this->Error('Incorrect unit: '.$unit);
	// Page sizes
	$this->StdPageSizes = array('a3'=>array(841.89, 1190.55), 'a4'=>array(595.28, 841.89), 'a5'=>array(420.94, 595.28),
		'letter'=>array(612, 792), 'legal'=>array(612, 1008));
	$size = $this->_getpagesize($size);
	$this->DefPageSize = $size;
	$this->CurPageSize = $size;
	// Page orientation
	$orientation = strtolower($orientation);
	if ($orientation == 'p' || $orientation == 'portrait')
	{
		$this->DefOrientation = 'P';
		$this->w = $size[0];
		$this->h = $size[1];
	}
	elseif ($orientation == 'l' || $orientation == 'landscape')
	{
		$this->DefOrientation = 'L';
		$this->w = $size[1];
		$this->h = $size[0];
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation = $this->DefOrientation;
	$this->wPt = astpp_n($this->w) * astpp_n($this->k);
	$this->hPt = astpp_n($this->h) * astpp_n($this->k);
	// Page margins (1 cm)
	$margin = astpp_div(28.35, $this->k);
	$this->SetMargins($margin, $margin);
	// Interior cell margin (1 mm)
	$this->cMargin = astpp_n($margin) / 10;
	// Line width (0.2 mm)
	$this->LineWidth = astpp_div(.567, $this->k);
	// Automatic page break
	$this->SetAutoPageBreak(true, 2 * astpp_n($margin));
	// Default display mode
	$this->SetDisplayMode('default');
	// Enable compression
	$this->SetCompression(true);
	// Set default PDF version number
	$this->PDFVersion = '1.3';
}

/**
 * @param double $left
 * @param double $top
 */
function SetMargins($left, $top, $right=null)
{
	// Set left, top and right margins
	$this->lMargin = $left;
	$this->tMargin = $top;
	if($right===null) {
			$right = $left;
	}
	$this->rMargin = $right;
}

function SetLeftMargin($margin)
{
	// Set left margin
	$this->lMargin = $margin;
	if (astpp_gt($this->page, 0) && $this->x < $margin)
		$this->x = $margin;
}

function SetTopMargin($margin)
{
	// Set top margin
	$this->tMargin = $margin;
}

function SetRightMargin($margin)
{
	// Set right margin
	$this->rMargin = $margin;
}

/**
 * @param boolean $auto
 */
function SetAutoPageBreak($auto, $margin=0)
{
	// Set auto page break mode and triggering margin
	$this->AutoPageBreak = $auto;
	$this->bMargin = $margin;
	$this->PageBreakTrigger = astpp_n($this->h)-astpp_n($margin);
}

/**
 * @param string $zoom
 */
function SetDisplayMode($zoom, $layout='default')
{
	// Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode = $zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode = $layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

/**
 * @param boolean $compress
 */
function SetCompression($compress)
{
	// Set page compression
	if (function_exists('gzcompress'))
		$this->compress = $compress;
	else
		$this->compress = false;
}

function SetTitle($title, $isUTF8 = false)
{
	// Title of document
	if ($isUTF8)
		$title = $this->_UTF8toUTF16($title);
	$this->title = $title;
}

function SetSubject($subject, $isUTF8 = false)
{
	// Subject of document
	if ($isUTF8)
		$subject = $this->_UTF8toUTF16($subject);
	$this->subject = $subject;
}

function SetAuthor($author, $isUTF8 = false)
{
	// Author of document
	if ($isUTF8)
		$author = $this->_UTF8toUTF16($author);
	$this->author = $author;
}

function SetKeywords($keywords, $isUTF8 = false)
{
	// Keywords of document
	if ($isUTF8)
		$keywords = $this->_UTF8toUTF16($keywords);
	$this->keywords = $keywords;
}

function SetCreator($creator, $isUTF8 = false)
{
	// Creator of document
	if ($isUTF8)
		$creator = $this->_UTF8toUTF16($creator);
	$this->creator = $creator;
}

function AliasNbPages($alias = '{nb}')
{
	// Define an alias for total number of pages
	$this->AliasNbPages = $alias;
}

function Error($msg)
{
	// Fatal error
	die('<b>FPDF error:</b> '.$msg);
}

function Open()
{
	// Begin document
	$this->state = 1;
}

function Close()
{
	// Terminate document
	if ($this->state == 3)
		return;
	if (astpp_eq0($this->page))
		$this->AddPage();
	// Page footer
	$this->InFooter = true;
	$this->Footer();
	$this->InFooter = false;
	// Close page
	$this->_endpage();
	// Close document
	$this->_enddoc();
}

function AddPage($orientation = '', $size = '')
{
	// Start a new page
	if (astpp_eq0($this->state))
		$this->Open();
	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->FontSizePt;
	$lw = $this->LineWidth;
	$dc = $this->DrawColor;
	$fc = $this->FillColor;
	$tc = $this->TextColor;
	$cf = $this->ColorFlag;
	if (astpp_gt($this->page, 0))
	{
		// Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		// Close page
		$this->_endpage();
	}
	// Start new page
	$this->_beginpage($orientation, $size);
	// Set line cap style to square
	$this->_out('2 J');
	// Set line width
	$this->LineWidth = $lw;
	$this->_out(sprintf('%.2F w', astpp_n($lw) * astpp_n($this->k)));
	// Set font
	if ($family)
		$this->SetFont($family, $style, $fontsize);
	// Set colors
	$this->DrawColor = $dc;
	if ($dc != '0 G')
		$this->_out($dc);
	$this->FillColor = $fc;
	if ($fc != '0 g')
		$this->_out($fc);
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
	// Page header
	$this->InHeader = true;
	$this->Header();
	$this->InHeader = false;
	// Restore line width
	if ($this->LineWidth != $lw)
	{
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w', astpp_n($lw) * astpp_n($this->k)));
	}
	// Restore font
	if ($family)
		$this->SetFont($family, $style, $fontsize);
	// Restore colors
	if ($this->DrawColor != $dc)
	{
		$this->DrawColor = $dc;
		$this->_out($dc);
	}
	if ($this->FillColor != $fc)
	{
		$this->FillColor = $fc;
		$this->_out($fc);
	}
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
}

function Header()
{
	// To be implemented in your own inherited class
}

function Footer()
{
	// To be implemented in your own inherited class
}

function PageNo()
{
	// Get current page number
	return $this->page;
}

function SetDrawColor($r, $g=null, $b=null)
{
	// Set color for all stroking operations
	if((astpp_eq0($r) && astpp_eq0($g) && astpp_eq0($b)) || $g===null)
		$this->DrawColor = sprintf('%.3F G',astpp_n($r)/255);
	else
		$this->DrawColor = sprintf('%.3F %.3F %.3F RG',astpp_n($r)/255,astpp_n($g)/255,astpp_n($b)/255);
	if(astpp_gt($this->page, 0))
		$this->_out($this->DrawColor);
}

/**
 * @param integer $r
 * @param integer $g
 * @param integer $b
 */
function SetFillColor($r, $g=null, $b=null)
{
	// Set color for all filling operations
	if((astpp_eq0($r) && astpp_eq0($g) && astpp_eq0($b)) || $g===null)
		$this->FillColor = sprintf('%.3F g',astpp_n($r)/255);
	else
		$this->FillColor = sprintf('%.3F %.3F %.3F rg',astpp_n($r)/255,astpp_n($g)/255,astpp_n($b)/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	if(astpp_gt($this->page, 0))
		$this->_out($this->FillColor);
}

function SetTextColor($r, $g=null, $b=null)
{
	// Set color for text
	if((astpp_eq0($r) && astpp_eq0($g) && astpp_eq0($b)) || $g===null)
		$this->TextColor = sprintf('%.3F g',astpp_n($r)/255);
	else
		$this->TextColor = sprintf('%.3F %.3F %.3F rg',astpp_n($r)/255,astpp_n($g)/255,astpp_n($b)/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

function GetStringWidth($s)
{
	// Get width of a string in the current font
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w = 0;
	$l = strlen($s);
	for($i=0;$i<$l;$i++) {
			$w = astpp_n($w) + astpp_n($cw[$s[$i]]);
	}
	return astpp_n($w)*astpp_n($this->FontSize)/1000;
}

function SetLineWidth($width)
{
	// Set line width
	$this->LineWidth = $width;
	if(astpp_gt($this->page, 0)) {
			$this->_out(sprintf('%.2F w',astpp_n($width)*astpp_n($this->k)));
	}
	}

/**
 * @param integer $x1
 * @param integer $x2
 */
function Line($x1, $y1, $x2, $y2)
{
	// Draw a line
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',astpp_n($x1)*astpp_n($this->k),(astpp_n($this->h)-astpp_n($y1))*astpp_n($this->k),astpp_n($x2)*astpp_n($this->k),(astpp_n($this->h)-astpp_n($y2))*astpp_n($this->k)));
}

function Rect($x, $y, $w, $h, $style='')
{
	// Draw a rectangle
	if($style=='F') {
			$op = 'f';
	} elseif($style=='FD' || $style=='DF') {
			$op = 'B';
	} else {
			$op = 'S';
	}
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',astpp_n($x)*astpp_n($this->k),(astpp_n($this->h)-astpp_n($y))*astpp_n($this->k),astpp_n($w)*astpp_n($this->k),-astpp_n($h)*astpp_n($this->k),$op));
}

/**
 * @param string $family
 */
function AddFont($family, $style='', $file='')
{
	// Add a TrueType, OpenType or Type1 font
	$family = strtolower($family);
	if($file=='') {
			$file = str_replace(' ','',$family).strtolower($style).'.php';
	}
	$style = strtoupper($style);
	if($style=='IB') {
			$style = 'BI';
	}
	$fontkey = $family.$style;
	if(isset($this->fonts[$fontkey])) {
			return;
	}
	$info = $this->_loadfont($file);
	$info['i'] = astpp_count($this->fonts)+1;
	if(!empty($info['diff']))
	{
		// Search existing encodings
		$n = astpp_array_search($info['diff'],$this->diffs);
		if(!$n)
		{
			$n = astpp_count($this->diffs)+1;
			$this->diffs[$n] = $info['diff'];
		}
		$info['diffn'] = $n;
	}
	if(!empty($info['file']))
	{
		// Embedded font
		if($info['type']=='TrueType') {
					$this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
		} else {
					$this->FontFiles[$info['file']] = array('length1'=>$info['size1'], 'length2'=>$info['size2']);
		}
	}
	$this->fonts[$fontkey] = $info;
}

/**
 * @param string $family
 */
function SetFont($family, $style='', $size=0)
{
	// Select a font; size given in points
	if($family=='') {
			$family = $this->FontFamily;
	} else {
			$family = strtolower($family);
	}
	$style = strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline = true;
		$style = str_replace('U','',$style);
	} else {
			$this->underline = false;
	}
	if($style=='IB') {
			$style = 'BI';
	}
	if(astpp_eq0($size)) {
			$size = $this->FontSizePt;
	}
	// Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size) {
			return;
	}
	// Test if font is already loaded
	$fontkey = $family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		// Test if one of the core fonts
		if($family=='arial') {
					$family = 'helvetica';
		}
		if(astpp_in_array($family,$this->CoreFonts))
		{
			if($family=='symbol' || $family=='zapfdingbats') {
							$style = '';
			}
			$fontkey = $family.$style;
			if(!isset($this->fonts[$fontkey])) {
							$this->AddFont($family,$style);
			}
		} else {
					$this->Error('Undefined font: '.$family.' '.$style);
		}
	}
	// Select it
	$this->FontFamily = $family;
	$this->FontStyle = $style;
	$this->FontSizePt = $size;
	$this->FontSize = astpp_div($size, $this->k);
	$this->CurrentFont = &$this->fonts[$fontkey];
	if (astpp_gt($this->page, 0))
		$this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
}

function SetFontSize($size)
{
	// Set font size in points
	if ($this->FontSizePt == $size)
		return;
	$this->FontSizePt = $size;
	$this->FontSize = astpp_div($size, $this->k);
	if (astpp_gt($this->page, 0))
		$this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
}

function AddLink()
{
	// Create a new internal link
	$n = astpp_count($this->links) + 1;
	$this->links[$n] = array(0, 0);
	return $n;
}

function SetLink($link, $y = 0, $page = -1)
{
	// Set destination of internal link
	if ($y == -1)
		$y = $this->y;
	if ($page == -1)
		$page = $this->page;
	$this->links[$link] = array($page, $y);
}

/**
 * @param double $y
 * @param string $link
 */
function Link($x, $y, $w, $h, $link)
{
	// Put a link on the page
	$this->PageLinks[$this->page][] = array(astpp_n($x) * astpp_n($this->k), astpp_n($this->hPt) - astpp_n($y) * astpp_n($this->k), astpp_n($w) * astpp_n($this->k), astpp_n($h) * astpp_n($this->k), $link);
}

function Text($x, $y, $txt)
{
	// Output a string
	$s = sprintf('BT %.2F %.2F Td (%s) Tj ET', astpp_n($x) * astpp_n($this->k), (astpp_n($this->h) - astpp_n($y)) * astpp_n($this->k), $this->_escape($txt));
	if ($this->underline && $txt != '')
		$s .= ' '.$this->_dounderline($x, $y, $txt);
	if ($this->ColorFlag)
		$s = 'q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function AcceptPageBreak()
{
	// Accept automatic page break or not
	return $this->AutoPageBreak;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	// Output a cell
	$k = $this->k;
	if(astpp_n($this->y)+astpp_n($h)>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		// Automatic page break
		$x = $this->x;
		$ws = $this->ws;
		if(astpp_gt($ws, 0))
		{
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageSize);
		$this->x = $x;
		if(astpp_gt($ws, 0))
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw',astpp_n($ws)*astpp_n($k)));
		}
	}
	if(astpp_eq0($w))
		$w = astpp_n($this->w)-astpp_n($this->rMargin)-astpp_n($this->x);
	$s = '';
	if($fill || $border==1)
	{
		if($fill)
			$op = ($border==1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',astpp_n($this->x)*astpp_n($k),(astpp_n($this->h)-astpp_n($this->y))*astpp_n($k),astpp_n($w)*astpp_n($k),-astpp_n($h)*astpp_n($k),$op);
	}
	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->y;
		if(strpos($border,'L')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',astpp_n($x)*astpp_n($k),(astpp_n($this->h)-astpp_n($y))*astpp_n($k),astpp_n($x)*astpp_n($k),(astpp_n($this->h)-(astpp_n($y)+astpp_n($h)))*astpp_n($k));
		if(strpos($border,'T')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',astpp_n($x)*astpp_n($k),(astpp_n($this->h)-astpp_n($y))*astpp_n($k),(astpp_n($x)+astpp_n($w))*astpp_n($k),(astpp_n($this->h)-astpp_n($y))*astpp_n($k));
		if(strpos($border,'R')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',(astpp_n($x)+astpp_n($w))*astpp_n($k),(astpp_n($this->h)-astpp_n($y))*astpp_n($k),(astpp_n($x)+astpp_n($w))*astpp_n($k),(astpp_n($this->h)-(astpp_n($y)+astpp_n($h)))*astpp_n($k));
		if(strpos($border,'B')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',astpp_n($x)*astpp_n($k),(astpp_n($this->h)-(astpp_n($y)+astpp_n($h)))*astpp_n($k),(astpp_n($x)+astpp_n($w))*astpp_n($k),(astpp_n($this->h)-(astpp_n($y)+astpp_n($h)))*astpp_n($k));
	}
	if($txt!=='')
	{
		if($align=='R')
			$dx = astpp_n($w)-astpp_n($this->cMargin)-astpp_n($this->GetStringWidth($txt));
		elseif($align=='C')
			$dx = (astpp_n($w)-astpp_n($this->GetStringWidth($txt)))/2;
		else
			$dx = $this->cMargin;
		if($this->ColorFlag)
			$s .= 'q '.$this->TextColor.' ';
		$txt2 = str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',(astpp_n($this->x)+astpp_n($dx))*astpp_n($k),(astpp_n($this->h)-(astpp_n($this->y)+.5*astpp_n($h)+.3*astpp_n($this->FontSize)))*astpp_n($k),$txt2);
		if($this->underline)
			$s .= ' '.$this->_dounderline(astpp_n($this->x)+astpp_n($dx),astpp_n($this->y)+.5*astpp_n($h)+.3*astpp_n($this->FontSize),$txt);
		if($this->ColorFlag)
			$s .= ' Q';
		if($link)
			$this->Link(astpp_n($this->x)+astpp_n($dx),astpp_n($this->y)+.5*astpp_n($h)-.5*astpp_n($this->FontSize),$this->GetStringWidth($txt),$this->FontSize,$link);
	}
	if($s)
		$this->_out($s);
	$this->lasth = $h;
	if(astpp_gt($ln, 0))
	{
		// Go to next line
		$this->y = astpp_n($this->y) + astpp_n($h);
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x = astpp_n($this->x) + astpp_n($w);
}

/**
 * @param integer $w
 * @param integer $h
 */
function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	// Output text with automatic or explicit line breaks
	$cw = &$this->CurrentFont['cw'];
	if(astpp_eq0($w))
		$w = astpp_n($this->w)-astpp_n($this->rMargin)-astpp_n($this->x);
	$wmax = astpp_div((astpp_n($w)-2*astpp_n($this->cMargin))*1000, $this->FontSize);
	$s = str_replace("\r",'',$txt);
	$nb = strlen($s);
	if(astpp_gt($nb, 0) && $s[astpp_n($nb)-1]=="\n")
		$nb--;
	$b = 0;
	if($border)
	{
		if($border==1)
		{
			$border = 'LTRB';
			$b = 'LRT';
			$b2 = 'LR';
		}
		else
		{
			$b2 = '';
			if (strpos($border, 'L') !== false)
				$b2 .= 'L';
			if (strpos($border, 'R') !== false)
				$b2 .= 'R';
			$b = (strpos($border, 'T') !== false) ? $b2.'T' : $b2;
		}
	}
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$ns = 0;
	$nl = 1;
	while ($i < $nb)
	{
		// Get next character
		$c = $s[$i];
		if ($c == "\n")
		{
			// Explicit line break
			if (astpp_gt($this->ws, 0))
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->Cell($w, $h, substr($s, $j, astpp_n($i) - astpp_n($j)), $b, 2, $align, $fill);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if ($border && $nl == 2)
				$b = $b2;
			continue;
		}
		if ($c == ' ')
		{
			$sep = $i;
			$ls = $l;
			$ns++;
		}
		$l = astpp_n($l) + astpp_n($cw[$c]);
		if ($l > $wmax)
		{
			// Automatic line break
			if ($sep == -1)
			{
				if ($i == $j)
					$i++;
				if (astpp_gt($this->ws, 0))
				{
					$this->ws = 0;
					$this->_out('0 Tw');
				}
				$this->Cell($w, $h, substr($s, $j, astpp_n($i) - astpp_n($j)), $b, 2, $align, $fill);
			}
			else
			{
				if ($align == 'J')
				{
					$this->ws = ($ns > 1) ? astpp_div((astpp_n($wmax) - astpp_n($ls)) / 1000 * astpp_n($this->FontSize), astpp_n($ns) - 1) : 0;
					$this->_out(sprintf('%.3F Tw', astpp_n($this->ws) * astpp_n($this->k)));
				}
				$this->Cell($w, $h, substr($s, $j, astpp_n($sep) - astpp_n($j)), $b, 2, $align, $fill);
				$i = astpp_n($sep) + 1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if ($border && $nl == 2)
				$b = $b2;
		}
		else
			$i++;
	}
	// Last chunk
	if (astpp_gt($this->ws, 0))
	{
		$this->ws = 0;
		$this->_out('0 Tw');
	}
	if ($border && strpos($border, 'B') !== false)
		$b .= 'B';
	$this->Cell($w, $h, substr($s, $j, astpp_n($i) - astpp_n($j)), $b, 2, $align, $fill);
	$this->x = $this->lMargin;
}

function Write($h, $txt, $link = '')
{
	// Output text in flowing mode
	$cw = &$this->CurrentFont['cw'];
	$w = astpp_n($this->w) - astpp_n($this->rMargin) - astpp_n($this->x);
	$wmax = astpp_div((astpp_n($w) - 2 * astpp_n($this->cMargin)) * 1000, $this->FontSize);
	$s = str_replace("\r", '', $txt);
	$nb = strlen($s);
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$nl = 1;
	while ($i < $nb)
	{
		// Get next character
		$c = $s[$i];
		if ($c == "\n")
		{
			// Explicit line break
			$this->Cell($w, $h, substr($s, $j, astpp_n($i) - astpp_n($j)), 0, 2, '', 0, $link);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			if ($nl == 1)
			{
				$this->x = $this->lMargin;
				$w = astpp_n($this->w) - astpp_n($this->rMargin) - astpp_n($this->x);
				$wmax = astpp_div((astpp_n($w) - 2 * astpp_n($this->cMargin)) * 1000, $this->FontSize);
			}
			$nl++;
			continue;
		}
		if ($c == ' ')
			$sep = $i;
		$l = astpp_n($l) + astpp_n($cw[$c]);
		if ($l > $wmax)
		{
			// Automatic line break
			if ($sep == -1)
			{
				if ($this->x > $this->lMargin)
				{
					// Move to next line
					$this->x = $this->lMargin;
					$this->y = astpp_n($this->y) + astpp_n($h);
					$w = astpp_n($this->w) - astpp_n($this->rMargin) - astpp_n($this->x);
					$wmax = astpp_div((astpp_n($w) - 2 * astpp_n($this->cMargin)) * 1000, $this->FontSize);
					$i++;
					$nl++;
					continue;
				}
				if ($i == $j)
					$i++;
				$this->Cell($w, $h, substr($s, $j, astpp_n($i) - astpp_n($j)), 0, 2, '', 0, $link);
			}
			else
			{
				$this->Cell($w, $h, substr($s, $j, astpp_n($sep) - astpp_n($j)), 0, 2, '', 0, $link);
				$i = astpp_n($sep) + 1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			if ($nl == 1)
			{
				$this->x = $this->lMargin;
				$w = astpp_n($this->w) - astpp_n($this->rMargin) - astpp_n($this->x);
				$wmax = astpp_div((astpp_n($w) - 2 * astpp_n($this->cMargin)) * 1000, $this->FontSize);
			}
			$nl++;
		}
		else
			$i++;
	}
	// Last chunk
	if ($i != $j)
		$this->Cell(astpp_n($l) / 1000 * astpp_n($this->FontSize), $h, substr($s, $j), 0, 0, '', 0, $link);
}

function Ln($h = null)
{
	// Line feed; default value is last cell height
	$this->x = $this->lMargin;
	if ($h === null)
		$this->y = astpp_n($this->y) + astpp_n($this->lasth);
	else
		$this->y = astpp_n($this->y) + astpp_n($h);
}

function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '')
{
	// Put an image on the page
	if ( ! isset($this->images[$file]))
	{
		// First use of this image, get info
		if ($type == '')
		{
			$pos = strrpos($file, '.');
			if ( ! $pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type = substr($file, astpp_n($pos) + 1);
		}
		$type = strtolower($type);
		if ($type == 'jpeg')
			$type = 'jpg';
		$mtd = '_parse'.$type;
		if ( ! method_exists($this, $mtd))
			$this->Error('Unsupported image type: '.$type);
		$info = $this->{$mtd}($file);
		$info['i'] = astpp_count($this->images) + 1;
		$this->images[$file] = $info;
	}
	else
		$info = $this->images[$file];

	// Automatic width and height calculation if needed
	if (astpp_eq0($w) && astpp_eq0($h))
	{
		// Put image at 96 dpi
		$w = -96;
		$h = -96;
	}
	if (astpp_lt($w, 0))
		$w = astpp_div(astpp_div(-astpp_n($info['w']) * 72, $w), $this->k);
	if (astpp_lt($h, 0))
		$h = astpp_div(astpp_div(-astpp_n($info['h']) * 72, $h), $this->k);
	if (astpp_eq0($w))
		$w = astpp_div(astpp_n($h) * astpp_n($info['w']), $info['h']);
	if (astpp_eq0($h))
		$h = astpp_div(astpp_n($w) * astpp_n($info['h']), $info['w']);

	// Flowing mode
	if ($y === null)
	{
		if (astpp_n($this->y) + astpp_n($h) > $this->PageBreakTrigger && ! $this->InHeader && ! $this->InFooter && $this->AcceptPageBreak())
		{
			// Automatic page break
			$x2 = $this->x;
			$this->AddPage($this->CurOrientation, $this->CurPageSize);
			$this->x = $x2;
		}
		$y = $this->y;
		$this->y = astpp_n($this->y) + astpp_n($h);
	}

	if ($x === null)
		$x = $this->x;
	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', astpp_n($w) * astpp_n($this->k), astpp_n($h) * astpp_n($this->k), astpp_n($x) * astpp_n($this->k), (astpp_n($this->h) - (astpp_n($y) + astpp_n($h))) * astpp_n($this->k), $info['i']));
	if ($link)
		$this->Link($x, $y, $w, $h, $link);
}

function GetX()
{
	// Get x position
	return $this->x;
}

function SetX($x)
{
	// Set x position
	if ($x >= 0)
		$this->x = $x;
	else
		$this->x = astpp_n($this->w) + astpp_n($x);
}

function GetY()
{
	// Get y position
	return $this->y;
}

function SetY($y)
{
	// Set y position and reset x
	$this->x = $this->lMargin;
	if ($y >= 0)
		$this->y = $y;
	else
		$this->y = astpp_n($this->h) + astpp_n($y);
}

/**
 * @param integer $x
 */
function SetXY($x, $y)
{
	// Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}

function Output($name = '', $dest = '')
{
	// Output PDF to some destination
	if ($this->state < 3)
		$this->Close();
	$dest = strtoupper($dest);
	if ($dest == '')
	{
		if ($name == '')
		{
			$name = 'doc.pdf';
			$dest = 'I';
		} else {
					$dest = 'F';
		}
	}
	switch ($dest)
	{
		case 'I':
			// Send to standard output
			$this->_checkoutput();
			if (PHP_SAPI != 'cli')
			{
				// We send to a browser
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="'.$name.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
			}
			echo $this->buffer;
			break;
		case 'D':
			// Download file
			$this->_checkoutput();
			header('Content-Type: application/pdf');
// 			header('Content-Type: application/x-download');
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'F':
			// Save to local file
			$f = fopen($name, 'wb');
			if ( ! $f)
				$this->Error('Unable to create output file: '.$name);
			fwrite($f, $this->buffer, strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			// Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/
function _dochecks()
{
	// Check availability of %F
	if (sprintf('%.1F', 1.0) != '1.0')
		$this->Error('This version of PHP is not supported');
	// Check mbstring overloading
	if (ini_get('mbstring.func_overload') & 2)
		$this->Error('mbstring overloading must be disabled');
	// Ensure runtime magic quotes are disabled
	if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) // PHP8: removed function
		@set_magic_quotes_runtime(0);
}

function _checkoutput()
{
	if (PHP_SAPI != 'cli')
	{
		if (headers_sent($file, $line))
			$this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
	}
	if (ob_get_length())
	{
		// The output buffer is not empty
		if (preg_match('/^(\xEF\xBB\xBF)?\s*$/', ob_get_contents()))
		{
			// It contains only a UTF-8 BOM and/or whitespace, let's clean it
			ob_clean();
		} else {
					$this->Error("Some data has already been output, can't send PDF file");
		}
	}
}

function _getpagesize($size)
{
	if (is_string($size))
	{
		$size = strtolower($size);
		if ( ! isset($this->StdPageSizes[$size]))
			$this->Error('Unknown page size: '.$size);
		$a = $this->StdPageSizes[$size];
		return array(astpp_div($a[0], $this->k), astpp_div($a[1], $this->k));
	}
	else
	{
		if ($size[0] > $size[1])
			return array($size[1], $size[0]);
		else
			return $size;
	}
}

/**
 * @param string $orientation
 * @param string $size
 */
function _beginpage($orientation, $size)
{
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->FontFamily = '';
	// Check page size and orientation
	if ($orientation == '')
		$orientation = $this->DefOrientation;
	else
		$orientation = strtoupper($orientation[0]);
	if ($size == '')
		$size = $this->DefPageSize;
	else
		$size = $this->_getpagesize($size);
	if ($orientation != $this->CurOrientation || $size[0] != $this->CurPageSize[0] || $size[1] != $this->CurPageSize[1])
	{
		// New size or orientation
		if ($orientation == 'P')
		{
			$this->w = $size[0];
			$this->h = $size[1];
		} else
		{
			$this->w = $size[1];
			$this->h = $size[0];
		}
		$this->wPt = astpp_n($this->w) * astpp_n($this->k);
		$this->hPt = astpp_n($this->h) * astpp_n($this->k);
		$this->PageBreakTrigger = astpp_n($this->h) - astpp_n($this->bMargin);
		$this->CurOrientation = $orientation;
		$this->CurPageSize = $size;
	}
	if ($orientation != $this->DefOrientation || $size[0] != $this->DefPageSize[0] || $size[1] != $this->DefPageSize[1])
		$this->PageSizes[$this->page] = array($this->wPt, $this->hPt);
}

function _endpage()
{
	$this->state = 1;
}

/**
 * @param string $font
 */
function _loadfont($font)
{
	// Load a font definition file from the font directory
	include($this->fontpath.$font);
	$a = get_defined_vars();
	if ( ! isset($a['name']))
		$this->Error('Could not include font definition file');
	return $a;
}

function _escape($s)
{
	// Escape special characters in strings
	$s = str_replace('\\', '\\\\', $s);
	$s = str_replace('(', '\\(', $s);
	$s = str_replace(')', '\\)', $s);
	$s = str_replace("\r", '\\r', $s);
	return $s;
}

function _textstring($s)
{
	// Format a text string
	return '('.$this->_escape($s).')';
}

function _UTF8toUTF16($s)
{
	// Convert UTF-8 to UTF-16BE with BOM
	$res = "\xFE\xFF";
	$nb = strlen($s);
	$i = 0;
	while ($i < $nb)
	{
		$c1 = ord($s[$i++]);
		if ($c1 >= 224)
		{
			// 3-byte character
			$c2 = ord($s[$i++]);
			$c3 = ord($s[$i++]);
			$res .= astpp_chr((($c1 & 0x0F) << 4) + (($c2 & 0x3C) >> 2));
			$res .= astpp_chr((($c2 & 0x03) << 6) + (astpp_n($c3 & 0x3F)));
		}
		elseif ($c1 >= 192)
		{
			// 2-byte character
			$c2 = ord($s[$i++]);
			$res .= astpp_chr(($c1 & 0x1C) >> 2);
			$res .= astpp_chr((($c1 & 0x03) << 6) + (astpp_n($c2 & 0x3F)));
		}
		else
		{
			// Single-byte character
			$res .= "\0".astpp_chr($c1);
		}
	}
	return $res;
}

function _dounderline($x, $y, $txt)
{
	// Underline text
	$up = $this->CurrentFont['up'];
	$ut = $this->CurrentFont['ut'];
	$w = astpp_n($this->GetStringWidth($txt)) + astpp_n($this->ws) * substr_count($txt, ' ');
	return sprintf('%.2F %.2F %.2F %.2F re f', astpp_n($x) * astpp_n($this->k), (astpp_n($this->h) - (astpp_n($y) - astpp_n($up) / 1000 * astpp_n($this->FontSize))) * astpp_n($this->k), astpp_n($w) * astpp_n($this->k), -astpp_n($ut) / 1000 * astpp_n($this->FontSizePt));
}

function _parsejpg($file)
{
	// Extract info from a JPEG file
	$a = getimagesize($file);
	if ( ! $a)
		$this->Error('Missing or incorrect image file: '.$file);
	if ($a[2] != 2)
		$this->Error('Not a JPEG file: '.$file);
	if ( ! isset($a['channels']) || $a['channels'] == 3)
		$colspace = 'DeviceRGB';
	elseif ($a['channels'] == 4)
		$colspace = 'DeviceCMYK';
	else
		$colspace = 'DeviceGray';
	$bpc = isset($a['bits']) ? $a['bits'] : 8;
	$data = file_get_contents($file);
	return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
}

/**
 * @param string $file
 */
function _parsepng($file)
{
	// Extract info from a PNG file
	$f = fopen($file, 'rb');
	if ( ! $f)
		$this->Error('Can\'t open image file: '.$file);
	$info = $this->_parsepngstream($f, $file);
	fclose($f);
	return $info;
}

function _parsepngstream($f, $file)
{
	// Check signature
	if ($this->_readstream($f, 8) != astpp_chr(137).'PNG'.astpp_chr(13).astpp_chr(10).astpp_chr(26).astpp_chr(10))
		$this->Error('Not a PNG file: '.$file);

	// Read header chunk
	$this->_readstream($f, 4);
	if ($this->_readstream($f, 4) != 'IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w = $this->_readint($f);
	$h = $this->_readint($f);
	$bpc = ord($this->_readstream($f, 1));
	if ($bpc > 8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct = ord($this->_readstream($f, 1));
	if (astpp_eq0($ct) || $ct == 4)
		$colspace = 'DeviceGray';
	elseif ($ct == 2 || $ct == 6)
		$colspace = 'DeviceRGB';
	elseif ($ct == 3)
		$colspace = 'Indexed';
	else
		$this->Error('Unknown color type: '.$file);
	if (ord($this->_readstream($f, 1)) != 0)
		$this->Error('Unknown compression method: '.$file);
	if (ord($this->_readstream($f, 1)) != 0)
		$this->Error('Unknown filter method: '.$file);
	if (ord($this->_readstream($f, 1)) != 0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f, 4);
	$dp = '/Predictor 15 /Colors '.($colspace == 'DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

	// Scan chunks looking for palette, transparency and image data
	$pal = '';
	$trns = '';
	$data = '';
	do
	{
		$n = $this->_readint($f);
		$type = $this->_readstream($f, 4);
		if ($type == 'PLTE')
		{
			// Read palette
			$pal = $this->_readstream($f, $n);
			$this->_readstream($f, 4);
		}
		elseif ($type == 'tRNS')
		{
			// Read transparency info
			$t = $this->_readstream($f, $n);
			if (astpp_eq0($ct))
				$trns = array(ord(substr($t, 1, 1)));
			elseif ($ct == 2)
				$trns = array(ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1)));
			else
			{
				$pos = strpos($t, astpp_chr(0));
				if ($pos !== false)
					$trns = array($pos);
			}
			$this->_readstream($f, 4);
		}
		elseif ($type == 'IDAT')
		{
			// Read image data block
			$data .= $this->_readstream($f, $n);
			$this->_readstream($f, 4);
		}
		elseif ($type == 'IEND')
			break;
		else
			$this->_readstream($f, astpp_n($n) + 4);
	}
	while ($n);

	if ($colspace == 'Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	$info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
	if ($ct >= 4)
	{
		// Extract alpha channel
		if ( ! function_exists('gzuncompress'))
			$this->Error('Zlib not available, can\'t handle alpha channel: '.$file);
		$data = gzuncompress($data);
		$color = '';
		$alpha = '';
		if ($ct == 4)
		{
			// Gray image
			$len = 2 * astpp_n($w);
			for ($i = 0; $i < $h; $i++)
			{
				$pos = (1 + astpp_n($len)) * astpp_n($i);
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data, astpp_n($pos) + 1, $len);
				$color .= preg_replace('/(.)./s', '$1', $line);
				$alpha .= preg_replace('/.(.)/s', '$1', $line);
			}
		} else
		{
			// RGB image
			$len = 4 * astpp_n($w);
			for ($i = 0; $i < $h; $i++)
			{
				$pos = (1 + astpp_n($len)) * astpp_n($i);
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data, astpp_n($pos) + 1, $len);
				$color .= preg_replace('/(.{3})./s', '$1', $line);
				$alpha .= preg_replace('/.{3}(.)/s', '$1', $line);
			}
		}
		unset($data);
		$data = gzcompress($color);
		$info['smask'] = gzcompress($alpha);
		if ($this->PDFVersion < '1.4')
			$this->PDFVersion = '1.4';
	}
	$info['data'] = $data;
	return $info;
}

function _readstream($f, $n)
{
	// Read n bytes from stream
	$res = '';
	while ($n > 0 && ! feof($f))
	{
		$s = fread($f, $n);
		if ($s === false)
			$this->Error('Error while reading stream');
		$n = astpp_n($n) - strlen($s);
		$res .= $s;
	}
	if ($n > 0)
		$this->Error('Unexpected end of stream');
	return $res;
}

function _readint($f)
{
	// Read a 4-byte integer from stream
	$a = unpack('Ni', $this->_readstream($f, 4));
	return $a['i'];
}

function _parsegif($file)
{
	// Extract info from a GIF file (via PNG conversion)
	if ( ! function_exists('imagepng'))
		$this->Error('GD extension is required for GIF support');
	if ( ! function_exists('imagecreatefromgif'))
		$this->Error('GD has no GIF read support');
	$im = imagecreatefromgif($file);
	if ( ! $im)
		$this->Error('Missing or incorrect image file: '.$file);
	imageinterlace($im, 0);
	$f = @fopen('php://temp', 'rb+');
	if ($f)
	{
		// Perform conversion in memory
		ob_start();
		imagepng($im);
		$data = ob_get_clean();
		PHP_VERSION_ID < 80000 && imagedestroy($im);
		fwrite($f, $data);
		rewind($f);
		$info = $this->_parsepngstream($f, $file);
		fclose($f);
	}
	else
	{
		// Use temporary file
		$tmp = tempnam('.', 'gif');
		if ( ! $tmp)
			$this->Error('Unable to create a temporary file');
		if ( ! imagepng($im, $tmp))
			$this->Error('Error while saving to temporary file');
		PHP_VERSION_ID < 80000 && imagedestroy($im);
		$info = $this->_parsepng($tmp);
		unlink($tmp);
	}
	return $info;
}

function _newobj()
{
	// Begin a new object
	$this->n++;
	$this->offsets[$this->n] = strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
}

function _putstream($s)
{
	$this->_out('stream');
	$this->_out($s);
	$this->_out('endstream');
}

function _out($s)
{
	// Add a line to the document
	if ($this->state == 2)
		$this->pages[$this->page] .= $s."\n";
	else
		$this->buffer .= $s."\n";
}

function _putpages()
{
	$nb = $this->page;
	if ( ! empty($this->AliasNbPages))
	{
		// Replace number of pages
		for ($n = 1; $n <= $nb; $n++)
			$this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
	}
	if ($this->DefOrientation == 'P')
	{
		$wPt = astpp_n($this->DefPageSize[0]) * astpp_n($this->k);
		$hPt = astpp_n($this->DefPageSize[1]) * astpp_n($this->k);
	}
	else
	{
		$wPt = astpp_n($this->DefPageSize[1]) * astpp_n($this->k);
		$hPt = astpp_n($this->DefPageSize[0]) * astpp_n($this->k);
	}
	$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
	for ($n = 1; $n <= $nb; $n++)
	{
		// Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		if (isset($this->PageSizes[$n]))
			$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageSizes[$n][0], $this->PageSizes[$n][1]));
		$this->_out('/Resources 2 0 R');
		if (isset($this->PageLinks[$n]))
		{
			// Links
			$annots = '/Annots [';
			foreach ($this->PageLinks[$n] as $pl)
			{
				$rect = sprintf('%.2F %.2F %.2F %.2F', $pl[0], $pl[1], astpp_n($pl[0]) + astpp_n($pl[2]), astpp_n($pl[1]) - astpp_n($pl[3]));
				$annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if (is_string($pl[4]))
					$annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l = $this->links[$pl[4]];
					$h = isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
					$annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>', 1 + 2 * astpp_n($l[0]), astpp_n($h) - astpp_n($l[1]) * astpp_n($this->k));
				}
			}
			$this->_out($annots.']');
		}
		if ($this->PDFVersion > '1.3')
			$this->_out('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
		$this->_out('/Contents '.(astpp_n($this->n) + 1).' 0 R>>');
		$this->_out('endobj');
		// Page content
		$p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	// Pages root
	$this->offsets[1] = strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids = '/Kids [';
	for ($i = 0; $i < $nb; $i++)
		$kids .= (3 + 2 * astpp_n($i)).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $wPt, $hPt));
	$this->_out('>>');
	$this->_out('endobj');
}

function _putfonts()
{
	$nf = $this->n;
	foreach ($this->diffs as $diff)
	{
		// Encodings
		$this->_newobj();
		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
		$this->_out('endobj');
	}
	foreach ($this->FontFiles as $file=>$info)
	{
		// Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n'] = $this->n;
		$font = file_get_contents($this->fontpath.$file, true);
		if ( ! $font)
			$this->Error('Font file not found: '.$file);
		$compressed = (substr($file, -2) == '.z');
		if ( ! $compressed && isset($info['length2']))
			$font = substr($font, 6, $info['length1']).substr($font, 6 + astpp_n($info['length1']) + 6, $info['length2']);
		$this->_out('<</Length '.strlen($font));
		if ($compressed)
			$this->_out('/Filter /FlateDecode');
		$this->_out('/Length1 '.$info['length1']);
		if (isset($info['length2']))
			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
		$this->_out('>>');
		$this->_putstream($font);
		$this->_out('endobj');
	}
	foreach ($this->fonts as $k=>$font)
	{
		// Font objects
		$this->fonts[$k]['n'] = astpp_n($this->n) + 1;
		$type = $font['type'];
		$name = $font['name'];
		if ($type == 'Core')
		{
			// Core font
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /Type1');
			if ($name != 'Symbol' && $name != 'ZapfDingbats')
				$this->_out('/Encoding /WinAnsiEncoding');
			$this->_out('>>');
			$this->_out('endobj');
		}
		elseif ($type == 'Type1' || $type == 'TrueType')
		{
			// Additional Type1 or TrueType/OpenType font
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /'.$type);
			$this->_out('/FirstChar 32 /LastChar 255');
			$this->_out('/Widths '.(astpp_n($this->n) + 1).' 0 R');
			$this->_out('/FontDescriptor '.(astpp_n($this->n) + 2).' 0 R');
			if (isset($font['diffn']))
				$this->_out('/Encoding '.(astpp_n($nf) + astpp_n($font['diffn'])).' 0 R');
			else
				$this->_out('/Encoding /WinAnsiEncoding');
			$this->_out('>>');
			$this->_out('endobj');
			// Widths
			$this->_newobj();
			$cw = &$font['cw'];
			$s = '[';
			for ($i = 32; $i <= 255; $i++)
				$s .= $cw[astpp_chr($i)].' ';
			$this->_out($s.']');
			$this->_out('endobj');
			// Descriptor
			$this->_newobj();
			$s = '<</Type /FontDescriptor /FontName /'.$name;
			foreach ($font['desc'] as $k=>$v)
				$s .= ' /'.$k.' '.$v;
			if ( ! empty($font['file']))
				$s .= ' /FontFile'.($type == 'Type1' ? '' : '2').' '.$this->FontFiles[$font['file']]['n'].' 0 R';
			$this->_out($s.'>>');
			$this->_out('endobj');
		}
		else
		{
			// Allow for additional types
			$mtd = '_put'.strtolower($type);
			if ( ! method_exists($this, $mtd))
				$this->Error('Unsupported font type: '.$type);
			$this->{$mtd}($font);
		}
	}
}

function _putimages()
{
	foreach (astpp_array_keys($this->images) as $file)
	{
		$this->_putimage($this->images[$file]);
		unset($this->images[$file]['data']);
		unset($this->images[$file]['smask']);
	}
}

function _putimage(&$info)
{
	$this->_newobj();
	$info['n'] = $this->n;
	$this->_out('<</Type /XObject');
	$this->_out('/Subtype /Image');
	$this->_out('/Width '.$info['w']);
	$this->_out('/Height '.$info['h']);
	if ($info['cs'] == 'Indexed')
		$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal']) / 3 - 1).' '.(astpp_n($this->n) + 1).' 0 R]');
	else
	{
		$this->_out('/ColorSpace /'.$info['cs']);
		if ($info['cs'] == 'DeviceCMYK')
			$this->_out('/Decode [1 0 1 0 1 0 1 0]');
	}
	$this->_out('/BitsPerComponent '.$info['bpc']);
	if (isset($info['f']))
		$this->_out('/Filter /'.$info['f']);
	if (isset($info['dp']))
		$this->_out('/DecodeParms <<'.$info['dp'].'>>');
	if (isset($info['trns']) && is_array($info['trns']))
	{
		$trns = '';
		for ($i = 0; $i < astpp_count($info['trns']); $i++)
			$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
		$this->_out('/Mask ['.$trns.']');
	}
	if (isset($info['smask']))
		$this->_out('/SMask '.(astpp_n($this->n) + 1).' 0 R');
	$this->_out('/Length '.strlen($info['data']).'>>');
	$this->_putstream($info['data']);
	$this->_out('endobj');
	// Soft mask
	if (isset($info['smask']))
	{
		$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
		$smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask']);
		$this->_putimage($smask);
	}
	// Palette
	if ($info['cs'] == 'Indexed')
	{
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
		$this->_putstream($pal);
		$this->_out('endobj');
	}
}

function _putxobjectdict()
{
	foreach ($this->images as $image)
		$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
}

function _putresourcedict()
{
	$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach ($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_out('>>');
	$this->_out('/XObject <<');
	$this->_putxobjectdict();
	$this->_out('>>');
}

function _putresources()
{
	$this->_putfonts();
	$this->_putimages();
	// Resource dictionary
	$this->offsets[2] = strlen($this->buffer);
	$this->_out('2 0 obj');
	$this->_out('<<');
	$this->_putresourcedict();
	$this->_out('>>');
	$this->_out('endobj');
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_textstring('FPDF '.FPDF_VERSION));
	if ( ! empty($this->title))
		$this->_out('/Title '.$this->_textstring($this->title));
	if ( ! empty($this->subject))
		$this->_out('/Subject '.$this->_textstring($this->subject));
	if ( ! empty($this->author))
		$this->_out('/Author '.$this->_textstring($this->author));
	if ( ! empty($this->keywords))
		$this->_out('/Keywords '.$this->_textstring($this->keywords));
	if ( ! empty($this->creator))
		$this->_out('/Creator '.$this->_textstring($this->creator));
	$this->_out('/CreationDate '.$this->_textstring('D:'.@date('YmdHis')));
}

function _putcatalog()
{
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if ($this->ZoomMode == 'fullpage')
		$this->_out('/OpenAction [3 0 R /Fit]');
	elseif ($this->ZoomMode == 'fullwidth')
		$this->_out('/OpenAction [3 0 R /FitH null]');
	elseif ($this->ZoomMode == 'real')
		$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif ( ! is_string($this->ZoomMode))
		$this->_out('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F', astpp_n($this->ZoomMode) / 100).']');
	if ($this->LayoutMode == 'single')
		$this->_out('/PageLayout /SinglePage');
	elseif ($this->LayoutMode == 'continuous')
		$this->_out('/PageLayout /OneColumn');
	elseif ($this->LayoutMode == 'two')
		$this->_out('/PageLayout /TwoColumnLeft');
}

function _putheader()
{
	$this->_out('%PDF-'.$this->PDFVersion);
}

function _puttrailer()
{
	$this->_out('/Size '.(astpp_n($this->n) + 1));
	$this->_out('/Root '.$this->n.' 0 R');
	$this->_out('/Info '.(astpp_n($this->n) - 1).' 0 R');
}

function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	// Info
	$this->_newobj();
	$this->_out('<<');
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');
	// Catalog
	$this->_newobj();
	$this->_out('<<');
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	// Cross-ref
	$o = strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.(astpp_n($this->n) + 1));
	$this->_out('0000000000 65535 f ');
	for ($i = 1; $i <= $this->n; $i++)
		$this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
	// Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
	$this->state = 3;
}
// End of class
}

// Handle special IE contype request
if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'contype')
{
	header('Content-Type: application/pdf');
	exit;
}

}

?>
