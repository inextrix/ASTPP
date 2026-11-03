<?php
/**
 * HTML2PDF Librairy - myPdf class
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @version   4.03
 */

require_once(dirname(__FILE__).'/tcpdfConfig.php');
require_once(dirname(__FILE__).'/../_tcpdf_'.HTML2PDF_USED_TCPDF_VERSION.'/tcpdf.php');

#[\AllowDynamicProperties]
class HTML2PDF_myPdf extends TCPDF
{
    protected $_footerParam = array();
    protected $_transf      = array();
    protected $_myLastPageGroup = null;
    protected $_myLastPageGroupNb = 0;

    // used to make a radius with bezier : (4/3 * (sqrt(2) - 1))
    const MY_ARC = 0.5522847498;

    // nb of segment to build a arc with bezier curv
    const ARC_NB_SEGMENT = 8;

    /**
     * class constructor
     *
     * @param string  $orientation page orientation, same as TCPDF
     * @param string  $unit        User measure unit, same as TCPDF
     * @param mixed   $format      The format used for pages, same as TCPDF
     * @param boolean $unicode     TRUE means that the input text is unicode (default = true)
     * @param String  $encoding    charset encoding; default is UTF-8
     * @param boolean $diskcache   if TRUE reduce the RAM memory usage by caching temporary data on filesystem (slower).
     * @access public
     */
    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'A4',
        $unicode = true,
        $encoding = 'UTF-8',
        $diskcache = false)
    {
        // call the parent constructor
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);

        // init the specific parameters used by HTML2PDF
        $this->SetCreator(PDF_CREATOR);
        $this->SetAutoPageBreak(false, 0);
        $this->linestyleCap = '2 J';
        $this->setPrintHeader(false);
        $this->jpeg_quality = 90;

        // prepare the automatic footer
        $this->SetMyFooter();

        $this->cMargin = 0;
    }

    /**
     * Set the parameters for the automatic footer
     *
     * @param boolean $page display the page number
     * @param boolean $date display the date
     * @param boolean $hour display the hour
     * @param boolean $form display a warning abour forms
     * @access public
     */
    public function SetMyFooter($page = false, $date = false, $hour = false, $form = false)
    {
        $page    = ($page ? true : false);
        $date    = ($date ? true : false);
        $hour    = ($hour ? true : false);
        $form    = ($form ? true : false);

        $this->_footerParam = array('page' => $page, 'date' => $date, 'hour' => $hour, 'form' => $form);
    }

    /**
     * This function is call automatically by TCPDF at the end of a page
     * It takes no parameters
     *
     * @access public
     */
    public function Footer()
    {
        // prepare the text from the tranlated text
        $txt = '';
        if ($this->_footerParam['form']) {
            $txt = (HTML2PDF_locale::get('pdf05'));
        }
        if ($this->_footerParam['date'] && $this->_footerParam['hour']) {
            $txt .= ($txt ? ' - ' : '').(HTML2PDF_locale::get('pdf03'));
        }
        if ($this->_footerParam['date'] && ! $this->_footerParam['hour']) {
            $txt .= ($txt ? ' - ' : '').(HTML2PDF_locale::get('pdf01'));
        }
        if ( ! $this->_footerParam['date'] && $this->_footerParam['hour']) {
            $txt .= ($txt ? ' - ' : '').(HTML2PDF_locale::get('pdf02'));
        }
        if ($this->_footerParam['page']) {
            $txt .= ($txt ? ' - ' : '').(HTML2PDF_locale::get('pdf04'));
        }

        if (strlen($txt) > 0) {
            // replace some values
            $toReplace = array(
                '[[date_d]]'  => date('d'),
                '[[date_m]]'  => date('m'),
                '[[date_y]]'  => date('Y'),
                '[[date_h]]'  => date('H'),
                '[[date_i]]'  => date('i'),
                '[[date_s]]'  => date('s'),
                '[[page_cu]]' => $this->getMyNumPage(),
                '[[page_nb]]' => $this->getMyAliasNbPages(),
            );
            $txt = str_replace(array_keys($toReplace), array_values($toReplace), $txt);

            // draw the footer
            parent::SetY(-11);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, $txt, 0, 0, 'R');
        }
    }

     /**
     * after cloning a object, we does not want to clone all the front informations
     * because it take a lot a time and a lot of memory => we use reference
     *
     * @param &HTML2PDF_myPdf object
     * @param HTML2PDF_myPdf $pdf
     * @access public
     */
    public function cloneFontFrom(&$pdf)
    {
        $this->fonts            = &$pdf->getFonts();
        $this->FontFiles        = &$pdf->getFontFiles();
        $this->diffs            = &$pdf->getDiffs();
        $this->fontlist         = &$pdf->getFontList();
        $this->numfonts         = &$pdf->getNumFonts();
        $this->fontkeys         = &$pdf->getFontKeys();
        $this->font_obj_ids     = &$pdf->getFontObjIds();
        $this->annotation_fonts = &$pdf->getAnnotFonts();
    }

    /**
     * multiple public accessor for some private attributs
     * used only by cloneFontFrom
     *
     * @return &array
     * @access public
     */
    public function &getFonts()
    {
        return $this->fonts;
    }
    public function &getFontFiles()
    {
        return $this->FontFiles;
    }
    public function &getDiffs()
    {
        return $this->diffs;
    }
    public function &getFontList()
    {
        return $this->fontlist;
    }
    public function &getNumFonts()
    {
        return $this->numfonts;
    }
    public function &getFontKeys()
    {
        return $this->fontkeys;
    }
    public function &getFontObjIds()
    {
        return $this->font_obj_ids;
    }
    public function &getAnnotFonts()
    {
        return $this->annotation_fonts;
    }

    /**
     * Verify that a Font is already loaded
     *
     * @param string Font Key
     * @return boolean
     * @access public
     */
    public function isLoadedFont($fontKey)
    {
        if (isset($this->fonts[$fontKey])) {
            return true;
        }

        if (isset($this->CoreFonts[$fontKey])) {
            return true;
        }

        return false;
    }

    /**
     * Get the Word Spacing
     *
     * @access public
     * @return float word spacing
     */
    public function getWordSpacing()
    {
        return $this->ws;
    }

    /**
     * set the Word Spacing
     *
     * @param float word spacing
     * @access public
     */
    public function setWordSpacing($ws = 0.)
    {
        $this->ws = $ws;
        $this->_out(sprintf('%.3F Tw', astpp_n($ws) * astpp_n($this->k)));
    }

    /**
     * start to use a rectangular Cliping Path with radius corners
     *
     * @param float $x (top left corner)
     * @param float $y (top left corner)
     * @param float $w (x+w  = botom rigth corner)
     * @param float $h (y+h = botom rigth corner)
     * @param array $cornerTL radius of the Top Left corner
     * @param array $cornerTR radius of the Top Right corner
     * @param array $cornerBL radius of the Bottom Left corner
     * @param array $cornerBR radius of the Bottom Right corner
     * @access public
     */
    public function clippingPathStart(
        $x = null,
        $y = null,
        $w = null,
        $h = null,
        $cornerTL = null,
        $cornerTR = null,
        $cornerBL = null,
        $cornerBR = null)
    {
        // init the path
        $path = '';

        // if we have the position and the size of the rectangle, we can proceed
        if ($x !== null && $y !== null && $w !== null && $h !== null) {
            // the positions of the rectangle's corners
            $x1 = astpp_n($x) * astpp_n($this->k);
            $y1 = (astpp_n($this->h) - astpp_n($y)) * astpp_n($this->k);

            $x2 = (astpp_n($x) + astpp_n($w)) * astpp_n($this->k);
            $y2 = (astpp_n($this->h) - astpp_n($y)) * astpp_n($this->k);

            $x3 = (astpp_n($x) + astpp_n($w)) * astpp_n($this->k);
            $y3 = (astpp_n($this->h) - astpp_n($y) - astpp_n($h)) * astpp_n($this->k);

            $x4 = astpp_n($x) * astpp_n($this->k);
            $y4 = (astpp_n($this->h) - astpp_n($y) - astpp_n($h)) * astpp_n($this->k);

            // if we have at least one radius corner, then we proceed to a specific path, else it is just a rectangle
            if ($cornerTL || $cornerTR || $cornerBL || $cornerBR) {
                // prepare the radius values
                if ($cornerTL) {
                    $cornerTL[0] = astpp_n($cornerTL[0]) * astpp_n($this->k);
                    $cornerTL[1] = -astpp_n($cornerTL[1]) * astpp_n($this->k);
                }
                if ($cornerTR) {
                    $cornerTR[0] = astpp_n($cornerTR[0]) * astpp_n($this->k);
                    $cornerTR[1] = -astpp_n($cornerTR[1]) * astpp_n($this->k);
                }
                if ($cornerBL) {
                    $cornerBL[0] = astpp_n($cornerBL[0]) * astpp_n($this->k);
                    $cornerBL[1] = -astpp_n($cornerBL[1]) * astpp_n($this->k);
                }
                if ($cornerBR) {
                    $cornerBR[0] = astpp_n($cornerBR[0]) * astpp_n($this->k);
                    $cornerBR[1] = -astpp_n($cornerBR[1]) * astpp_n($this->k);
                }

                // if TL radius then specific start else (X1,Y1)
                if ($cornerTL) {
                    $path .= sprintf('%.2F %.2F m ', astpp_n($x1) + astpp_n($cornerTL[0]), $y1);
                } else {
                    $path .= sprintf('%.2F %.2F m ', $x1, $y1);
                }

                // if TR radius then line + arc, else line to (X2,Y2)
                if ($cornerTR) {
                    $xt1 = (astpp_n($x2) - astpp_n($cornerTR[0])) + astpp_n($cornerTR[0]) * self::MY_ARC;
                    $yt1 = (astpp_n($y2) + astpp_n($cornerTR[1])) - astpp_n($cornerTR[1]);
                    $xt2 = (astpp_n($x2) - astpp_n($cornerTR[0])) + astpp_n($cornerTR[0]);
                    $yt2 = (astpp_n($y2) + astpp_n($cornerTR[1])) - astpp_n($cornerTR[1]) * self::MY_ARC;

                    $path .= sprintf('%.2F %.2F l ', astpp_n($x2) - astpp_n($cornerTR[0]), $y2);
                    $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x2, astpp_n($y2) + astpp_n($cornerTR[1]));
                } else {
                    $path .= sprintf('%.2F %.2F l ', $x2, $y2);
                }

                // if BR radius then line + arc, else line to (X3, Y3)
                if ($cornerBR) {
                    $xt1 = (astpp_n($x3) - astpp_n($cornerBR[0])) + astpp_n($cornerBR[0]);
                    $yt1 = (astpp_n($y3) - astpp_n($cornerBR[1])) + astpp_n($cornerBR[1]) * self::MY_ARC;
                    $xt2 = (astpp_n($x3) - astpp_n($cornerBR[0])) + astpp_n($cornerBR[0]) * self::MY_ARC;
                    $yt2 = (astpp_n($y3) - astpp_n($cornerBR[1])) + astpp_n($cornerBR[1]);

                    $path .= sprintf('%.2F %.2F l ', $x3, astpp_n($y3) - astpp_n($cornerBR[1]));
                    $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, astpp_n($x3) - astpp_n($cornerBR[0]), $y3);
                } else {
                    $path .= sprintf('%.2F %.2F l ', $x3, $y3);
                }

                // if BL radius then line + arc, else line to (X4, Y4)
                if ($cornerBL) {
                    $xt1 = (astpp_n($x4) + astpp_n($cornerBL[0])) - astpp_n($cornerBL[0]) * self::MY_ARC;
                    $yt1 = (astpp_n($y4) - astpp_n($cornerBL[1])) + astpp_n($cornerBL[1]);
                    $xt2 = (astpp_n($x4) + astpp_n($cornerBL[0])) - astpp_n($cornerBL[0]);
                    $yt2 = (astpp_n($y4) - astpp_n($cornerBL[1])) + astpp_n($cornerBL[1]) * self::MY_ARC;

                    $path .= sprintf('%.2F %.2F l ', astpp_n($x4) + astpp_n($cornerBL[0]), $y4);
                    $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x4, astpp_n($y4) - astpp_n($cornerBL[1]));
                } else {
                    $path .= sprintf('%.2F %.2F l ', $x4, $y4);
                }

                // if RL radius then line + arc
                if ($cornerTL) {
                    $xt1 = (astpp_n($x1) + astpp_n($cornerTL[0])) - astpp_n($cornerTL[0]);
                    $yt1 = (astpp_n($y1) + astpp_n($cornerTL[1])) - astpp_n($cornerTL[1]) * self::MY_ARC;
                    $xt2 = (astpp_n($x1) + astpp_n($cornerTL[0])) - astpp_n($cornerTL[0]) * self::MY_ARC;
                    $yt2 = (astpp_n($y1) + astpp_n($cornerTL[1])) - astpp_n($cornerTL[1]);

                    $path .= sprintf('%.2F %.2F l ', $x1, astpp_n($y1) + astpp_n($cornerTL[1]));
                    $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, astpp_n($x1) + astpp_n($cornerTL[0]), $y1);
                }
            } else {
                $path .= sprintf('%.2F %.2F m ', $x1, $y1);
                $path .= sprintf('%.2F %.2F l ', $x2, $y2);
                $path .= sprintf('%.2F %.2F l ', $x3, $y3);
                $path .= sprintf('%.2F %.2F l ', $x4, $y4);
            }

            // close the path
            $path .= ' h W n';
        }

        // using the path as a clipping path
        $this->_out('q '.$path.' ');
    }

    /**
     * stop to use the Cliping Path
     *
     * @access public
     */
    public function clippingPathStop()
    {
        $this->_out(' Q');
    }

    /**
     * draw a filled corner of a border with a external and a internal radius
     *         /--------+ ext2
     *        /         |
     *       /  /-------+ int2
     *      /  /
     *      | /
     *      | |
     *      | |
     * ext1 +-+ int1        + cen
     *
     * @param float $ext1X
     * @param float $ext1Y
     * @param float $ext2X
     * @param float $ext2Y
     * @param float $int1X
     * @param float $int1Y
     * @param float $int2X
     * @param float $int2Y
     * @param float $cenX
     * @param float $cenY
     * @access public
     */
    public function drawCurve($ext1X, $ext1Y, $ext2X, $ext2Y, $int1X, $int1Y, $int2X, $int2Y, $cenX, $cenY)
    {
        // prepare the coordinates
        $ext1X = astpp_n($ext1X) * astpp_n($this->k);
        $ext2X = astpp_n($ext2X) * astpp_n($this->k);
        $int1X = astpp_n($int1X) * astpp_n($this->k);
        $int2X = astpp_n($int2X) * astpp_n($this->k);
        $cenX  = astpp_n($cenX) * astpp_n($this->k);

        $ext1Y = (astpp_n($this->h) - astpp_n($ext1Y)) * astpp_n($this->k);
        $ext2Y = (astpp_n($this->h) - astpp_n($ext2Y)) * astpp_n($this->k);
        $int1Y = (astpp_n($this->h) - astpp_n($int1Y)) * astpp_n($this->k);
        $int2Y = (astpp_n($this->h) - astpp_n($int2Y)) * astpp_n($this->k);
        $cenY  = (astpp_n($this->h) - astpp_n($cenY)) * astpp_n($this->k);

        // init the curve
        $path = '';

        if (astpp_n($ext1X) - astpp_n($cenX) != 0) {
            $xt1 = astpp_n($cenX) + (astpp_n($ext1X) - astpp_n($cenX));
            $yt1 = astpp_n($cenY) + (astpp_n($ext2Y) - astpp_n($cenY)) * self::MY_ARC;
            $xt2 = astpp_n($cenX) + (astpp_n($ext1X) - astpp_n($cenX)) * self::MY_ARC;
            $yt2 = astpp_n($cenY) + (astpp_n($ext2Y) - astpp_n($cenY));
        } else {
            $xt1 = astpp_n($cenX) + (astpp_n($ext2X) - astpp_n($cenX)) * self::MY_ARC;
            $yt1 = astpp_n($cenY) + (astpp_n($ext1Y) - astpp_n($cenY));
            $xt2 = astpp_n($cenX) + (astpp_n($ext2X) - astpp_n($cenX));
            $yt2 = astpp_n($cenY) + (astpp_n($ext1Y) - astpp_n($cenY)) * self::MY_ARC;
        }
        $path .= sprintf('%.2F %.2F m ', $ext1X, $ext1Y);
        $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2X, $ext2Y);

        if (astpp_n($int1X) - astpp_n($cenX) != 0) {
            $xt1 = astpp_n($cenX) + (astpp_n($int1X) - astpp_n($cenX)) * self::MY_ARC;
            $yt1 = astpp_n($cenY) + (astpp_n($int2Y) - astpp_n($cenY));
            $xt2 = astpp_n($cenX) + (astpp_n($int1X) - astpp_n($cenX));
            $yt2 = astpp_n($cenY) + (astpp_n($int2Y) - astpp_n($cenY)) * self::MY_ARC;
        } else {
            $xt1 = astpp_n($cenX) + (astpp_n($int2X) - astpp_n($cenX));
            $yt1 = astpp_n($cenY) + (astpp_n($int1Y) - astpp_n($cenY)) * self::MY_ARC;
            $xt2 = astpp_n($cenX) + (astpp_n($int2X) - astpp_n($cenX)) * self::MY_ARC;
            $yt2 = astpp_n($cenY) + (astpp_n($int1Y) - astpp_n($cenY));
        }
        $path .= sprintf('%.2F %.2F l ', $int2X, $int2Y);
        $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $int1X, $int1Y);

        // draw the curve
        $this->_out($path.'f');
    }

    /**
     * draw a filled corner of a border with only a external radius
     *         /--+ ext2
     *        /   |
     *       /    |
     *      /     |
     *      |     |
     *      |     |
     *      |     |
     * ext1 +-----+ int      + cen
     *
     * @param float $ext1X
     * @param float $ext1Y
     * @param float $ext2X
     * @param float $ext2Y
     * @param float $intX
     * @param float $intY
     * @param float $cenX
     * @param float $cenY
     * @access public
     */
    public function drawCorner($ext1X, $ext1Y, $ext2X, $ext2Y, $intX, $intY, $cenX, $cenY)
    {
        // prepare the coordinates
        $ext1X = astpp_n($ext1X) * astpp_n($this->k);
        $ext2X = astpp_n($ext2X) * astpp_n($this->k);
        $intX  = astpp_n($intX) * astpp_n($this->k);
        $cenX  = astpp_n($cenX) * astpp_n($this->k);

        $ext1Y = (astpp_n($this->h) - astpp_n($ext1Y)) * astpp_n($this->k);
        $ext2Y = (astpp_n($this->h) - astpp_n($ext2Y)) * astpp_n($this->k);
        $intY  = (astpp_n($this->h) - astpp_n($intY)) * astpp_n($this->k);
        $cenY  = (astpp_n($this->h) - astpp_n($cenY)) * astpp_n($this->k);

        // init the curve
        $path = '';

        if (astpp_n($ext1X) - astpp_n($cenX) != 0) {
            $xt1 = astpp_n($cenX) + (astpp_n($ext1X) - astpp_n($cenX));
            $yt1 = astpp_n($cenY) + (astpp_n($ext2Y) - astpp_n($cenY)) * self::MY_ARC;
            $xt2 = astpp_n($cenX) + (astpp_n($ext1X) - astpp_n($cenX)) * self::MY_ARC;
            $yt2 = astpp_n($cenY) + (astpp_n($ext2Y) - astpp_n($cenY));
        } else {
            $xt1 = astpp_n($cenX) + (astpp_n($ext2X) - astpp_n($cenX)) * self::MY_ARC;
            $yt1 = astpp_n($cenY) + (astpp_n($ext1Y) - astpp_n($cenY));
            $xt2 = astpp_n($cenX) + (astpp_n($ext2X) - astpp_n($cenX));
            $yt2 = astpp_n($cenY) + (astpp_n($ext1Y) - astpp_n($cenY)) * self::MY_ARC;
        }
        $path .= sprintf('%.2F %.2F m ', $ext1X, $ext1Y);
        $path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2X, $ext2Y);
        $path .= sprintf('%.2F %.2F l ', $intX, $intY);
        $path .= sprintf('%.2F %.2F l ', $ext1X, $ext1Y);

        // draw the curve
        $this->_out($path.'f');
    }

    /**
     * Start a transformation
     *
     * @access public
     */
    public function startTransform()
    {
        $this->_out('q');
    }

    /**
     * Stop a transformation
     *
     * @access public
     */
    public function stopTransform()
    {
        $this->_out('Q');
    }

    /**
     * add a Translate transformation
     *
     * @access public
     */
    public function setTranslate($xT, $yT)
    {
        // Matrix for Translate
        $tm[0] = 1;
        $tm[1] = 0;
        $tm[2] = 0;
        $tm[3] = 1;
        $tm[4] = astpp_n($xT) * astpp_n($this->k);
        $tm[5] = -astpp_n($yT) * astpp_n($this->k);

        // apply the Transform Matric
        $this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
    }

    /**
     * add a Rotate transformation
     *
     * @param float $angle
     * @access public
     */
    public function setRotation($angle, $xC = null, $yC = null)
    {
        // if no center, rotate around the current posiition
        if ($xC === null) $xC = $this->x;
        if ($yC === null) $yC = $this->y;

        // prepare the coordinate
        $yC = (astpp_n($this->h) - astpp_n($yC)) * astpp_n($this->k);
        $xC = astpp_n($xC) * astpp_n($this->k);

        // Matrix for Rotate
        $tm[0] = cos(deg2rad($angle));
        $tm[1] = sin(deg2rad($angle));
        $tm[2] = -astpp_n($tm[1]);
        $tm[3] = $tm[0];
        $tm[4] = astpp_n($xC) + astpp_n($tm[1]) * astpp_n($yC) - astpp_n($tm[0]) * astpp_n($xC);
        $tm[5] = astpp_n($yC) - astpp_n($tm[0]) * astpp_n($yC) - astpp_n($tm[1]) * astpp_n($xC);

        // apply the Transform Matric
        $this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
    }

    /**
     * we redifine the original SetX method, because we don't want the automatic treatment.
     * It is HTML2PDF that make the treatment
     *
     * @param float   $x
     * @param boolean $rtloff NOT USED
     * @access public
     */
    public function SetX($x, $rtloff = false)
    {
        $this->x = $x;
    }

    /**
     * we redifine the original SetY method, because we don't want the automatic treatment.
     * It is HTML2PDF that make the treatment
     *
     * @param float   $y
     * @param boolean $resetx Reset the X position
     * @param boolean $rtloff NOT USED
     * @access public
     */
    public function SetY($y, $resetx = true, $rtloff = false)
    {
        if ($resetx)
            $this->x = $this->lMargin;

        $this->y = $y;
    }

    /**
     * we redifine the original SetXY method, because we don't want the automatic treatment.
     * It is HTML2PDF that make the treatment
     *
     * @param integer $x
     * @param integer $y
     * @param boolean $rtloff NOT USED
     * @access public
     */
    public function SetXY($x, $y, $rtloff = false)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * multiple public accessor because HTML2PDF need to use TCPDF without being a extend of it
     *
     * @param  mixed
     * @return mixed
     * @access public
     */
    public function getK()
    {
        return $this->k;
    }

    /**
     * @return double
     */
    public function getW()
    {
        return $this->w;
    }

    /**
     * @return double
     */
    public function getH()
    {
        return $this->h;
    }
    public function getlMargin()
    {
        return $this->lMargin;
    }

    /**
     * @return double
     */
    public function getrMargin()
    {
        return $this->rMargin;
    }

    /**
     * @return double
     */
    public function gettMargin()
    {
        return $this->tMargin;
    }
    public function getbMargin()
    {
        return $this->bMargin;
    }
    public function setbMargin($v)
    {
        $this->bMargin = $v;
    }

    /**
     * SVG - Convert a SVG Style in PDF Style
     *
     * @param  array  $styles SVG Style
     * @return string PDF style
     * @access public
     */
    public function svgSetStyle($styles)
    {
        // init the PDF style
        $style = '';

        // Style : fill
        if ($styles['fill']) {
            $this->setFillColorArray($styles['fill']);
            $style .= 'F';
        }

        // Style : stroke
        if ($styles['stroke'] && $styles['stroke-width']) {
            $this->SetDrawColorArray($styles['stroke']);
            $this->SetLineWidth($styles['stroke-width']);
            $style .= 'D';
        }

        // Style : opacity
        if ($styles['fill-opacity']) {
            $this->SetAlpha($styles['fill-opacity']);
        }

        return $style;
    }

    /**
     * SVG - make a Rectangle
     *
     * @param float $x
     * @param float $y
     * @param float $w
     * @param float $h
     * @param string $style PDF Style
     * @access public
     */
    public function svgRect($x, $y, $w, $h, $style)
    {
        // prepare the 4 corners
        $x1 = $x;
        $x2 = astpp_n($x) + astpp_n($w);
        $x3 = astpp_n($x) + astpp_n($w);
        $x4 = $x;

        $y1 = $y;
        $y2 = $y;
        $y3 = astpp_n($y) + astpp_n($h);
        $y4 = astpp_n($y) + astpp_n($h);

        // get the Closing operator from the PDF Style
        if ($style == 'F') $op = 'f';
        elseif ($style == 'FD' || $style == 'DF') $op = 'B';
        else $op = 'S';

        // drawing
        $this->_Point($x1, $y1, true);
        $this->_Line($x2, $y2, true);
        $this->_Line($x3, $y3, true);
        $this->_Line($x4, $y4, true);
        $this->_Line($x1, $y1, true);
        $this->_out($op);
    }

    /**
     * SVG - make a Line
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @access public
     */
    public function svgLine($x1, $y1, $x2, $y2)
    {
        // get the Closing operator
        $op = 'S';

        // drawing
        $this->_Point($x1, $y1, true);
        $this->_Line($x2, $y2, true);
        $this->_out($op);
    }

    /**
     * SVG - make a Ellipse
     *
     * @param float  $x0 x Center
     * @param float  $y0 y Center
     * @param float  $rx x radius
     * @param float  $ry y radius
     * @param string $style PDF Style
     * @access public
     */
    public function svgEllipse($x0, $y0, $rx, $ry, $style)
    {
        // get the Closing operator from the PDF Style
        if ($style == 'F') $op = 'f';
        elseif ($style == 'FD' || $style == 'DF') $op = 'B';
        else $op = 'S';

        // drawing
        $this->_Arc($x0, $y0, $rx, $ry, 0, 2 * M_PI, true, true, true);
        $this->_out($op);
    }

    /**
     * SVG - make a Advanced Polygone
     *
     * @param array  $actions list of actions
     * @param string $style PDF Style
     * @access public
     */
    public function svgPolygone($actions, $style)
    {
        // get the Closing operator from the PDF Style
        if ($style == 'F') $op = 'f';
        elseif ($style == 'FD' || $style == 'DF') $op = 'B';
        else $op = 'S';

        // To save the First action and the last point
        $first = array('', 0, 0);
        $last = array(0, 0, 0, 0);

        foreach ($actions as $action) {
            switch ($action[0])
            {
                // Start the Path
                case 'M':
                case 'm':
                    $first = $action;
                    $x = $action[1]; $y = $action[2]; $xc = $x; $yc = $y;
                    $this->_Point($x, $y, true);
                    break;

                // Close the Path
                case 'Z':
                case 'z':
                    $x = $first[1]; $y = $first[2]; $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Line (new point)
                case 'L':
                    $x = $action[1]; $y = $action[2]; $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Line (vector from last point)
                case 'l':
                    $x = astpp_n($last[0]) + astpp_n($action[1]); $y = astpp_n($last[1]) + astpp_n($action[2]); $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Horizontal Line (new point)
                case 'H':
                    $x = $action[1]; $y = $last[1]; $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Horisontal Line (vector from last point)
                case 'h':
                    $x = astpp_n($last[0]) + astpp_n($action[1]); $y = $last[1]; $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Vertical Line (new point)
                case 'V':
                    $x = $last[0]; $y = $action[1]; $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Vertical Line (vector from last point)
                case 'v':
                    $x = $last[0]; $y = astpp_n($last[1]) + astpp_n($action[1]); $xc = $x; $yc = $y;
                    $this->_Line($x, $y, true);
                    break;

                // Make a Arc (new point)
                case 'A':
                    $rx = $action[1]; // rx
                    $ry = $action[2]; // ry
                    $a = $action[3]; // deviation angle of the axis X
                    $l = $action[4]; // large-arc-flag
                    $s = $action[5]; // sweep-flag
                    $x1 = $last[0]; // begin x
                    $y1 = $last[1]; // begin y
                    $x2 = $action[6]; // final x
                    $y2 = $action[7]; // final y

                    $this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
                    $x = $x2; $y = $y2; $xc = $x; $yc = $y;
                    break;

                // Make a Arc (vector from last point)
                case 'a':
                    $rx = $action[1]; // rx
                    $ry = $action[2]; // ry
                    $a = $action[3]; // deviation angle of the axis X
                    $l = $action[4]; // large-arc-flag
                    $s = $action[5]; // sweep-flag
                    $x1 = $last[0]; // begin x
                    $y1 = $last[1]; // begin y
                    $x2 = astpp_n($last[0]) + astpp_n($action[6]); // final x
                    $y2 = astpp_n($last[1]) + astpp_n($action[7]); // final y

                    $this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
                    $x = $x2; $y = $y2; $xc = $x; $yc = $y;
                    break;

                // Make a Bezier Curve (new point)
                case 'C':
                    $x1 = $action[1];
                    $y1 = $action[2];
                    $x2 = $action[3];
                    $y2 = $action[4];
                    $xf = $action[5];
                    $yf = $action[6];
                    $this->_Curve($x1, $y1, $x2, $y2, $xf, $yf, true);
                    $x = $xf; $y = $yf; $xc = $x2; $yc = $y2;
                    break;

                // Make a Bezier Curve (vector from last point)
                case 'c':
                    $x1 = astpp_n($last[0]) + astpp_n($action[1]);
                    $y1 = astpp_n($last[1]) + astpp_n($action[2]);
                    $x2 = astpp_n($last[0]) + astpp_n($action[3]);
                    $y2 = astpp_n($last[1]) + astpp_n($action[4]);
                    $xf = astpp_n($last[0]) + astpp_n($action[5]);
                    $yf = astpp_n($last[1]) + astpp_n($action[6]);
                    $this->_Curve($x1, $y1, $x2, $y2, $xf, $yf, true);
                    $x = $xf; $y = $yf; $xc = $x2; $yc = $y2;
                    break;

                // Unknown Path
                default:
                    throw new HTML2PDF_exception(0, 'SVG Path Error : ['.$action[0].'] unkown');
            }

            // save the last point
            $last = array($x, $y, $xc, $yc);
        }

        // finish the path
        $this->_out($op);
    }

    /**
     * SVG - go to a point
     *
     * @param float   $x
     * @param float   $y
     * @param boolean $trans apply transformation
     * @access protected
     */
    protected function _Point($x, $y, $trans = false)
    {
        if ($trans) {
        	$this->ptTransform($x, $y);
        }

        $this->_out(sprintf('%.2F %.2F m', $x, $y));
    }

    /**
     * SVG - make a line from the last point to (x,y)
     *
     * @param float   $x
     * @param float   $y
     * @param boolean $trans apply transformation
     * @access protected
     */
    protected function _Line($x, $y, $trans = false)
    {
        if ($trans) {
        	$this->ptTransform($x, $y);
        }

        $this->_out(sprintf('%.2F %.2F l', $x, $y));
    }

    /**
     * SVG - make a bezier curve from the last point to (xf,yf), with the 2 direction points (x1,y1) and (x2,y2)
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $xf
     * @param float $yf
     * @param boolean $trans apply transformation
     * @access protected
     */
    protected function _Curve($x1, $y1, $x2, $y2, $xf, $yf, $trans = false)
    {
        if ($trans) {
            $this->ptTransform($x1, $y1);
            $this->ptTransform($x2, $y2);
            $this->ptTransform($xf, $yf);
        }
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1, $y1, $x2, $y2, $xf, $yf));
    }

    /**
     * SVG - make a arc with Center, Radius, from angleBegin to angleEnd
     *
     * @param float $xc
     * @param float $yc
     * @param float $rx
     * @param float $ry
     * @param float $angleBegin in radians
     * @param float $angleEnd in radians
     * @param boolean $direction
     * @param boolean $drawFirst, true => add the first point
     * @param boolean $trans apply transformation
     * @access protected
     */
    protected function _Arc(
        $xc,
        $yc,
        $rx,
        $ry,
        $angleBegin,
        $angleEnd,
        $direction = true,
        $drawFirst = true,
        $trans = false)
    {
        // if we want the no trigo direction : add 2PI to the begin angle, to invert the direction
        if ( ! $direction) $angleBegin = astpp_n($angleBegin) + astpp_n(M_PI * 2.);

        // cut in segment to convert in berize curv
        $dt = astpp_div(astpp_n($angleEnd) - astpp_n($angleBegin), self::ARC_NB_SEGMENT);
        $dtm = astpp_n($dt) / 3;

        // center of the arc
        $x0 = $xc; $y0 = $yc;

        // calculing the first point
        $t1 = $angleBegin;
        $a0 = astpp_n($x0) + (astpp_n($rx) * cos($t1));
        $b0 = astpp_n($y0) + (astpp_n($ry) * sin($t1));
        $c0 = -astpp_n($rx) * sin($t1);
        $d0 = astpp_n($ry) * cos($t1);

        // if drawFirst => draw the first point
        if ($drawFirst) {
        	$this->_Point($a0, $b0, $trans);
        }

        // foreach segment
        for ($i = 1; $i <= self::ARC_NB_SEGMENT; $i++) {
            // calculing the next point
            $t1 = (astpp_n($i) * astpp_n($dt)) + astpp_n($angleBegin);
            $a1 = astpp_n($x0) + (astpp_n($rx) * cos($t1));
            $b1 = astpp_n($y0) + (astpp_n($ry) * sin($t1));
            $c1 = -astpp_n($rx) * sin($t1);
            $d1 = astpp_n($ry) * cos($t1);

            // make the bezier curv
            $this->_Curve(
                astpp_n($a0) + (astpp_n($c0) * astpp_n($dtm)), astpp_n($b0) + (astpp_n($d0) * astpp_n($dtm)),
                astpp_n($a1) - (astpp_n($c1) * astpp_n($dtm)), astpp_n($b1) - (astpp_n($d1) * astpp_n($dtm)),
                $a1, $b1,
                $trans
            );

            // save the point
            $a0 = $a1;
            $b0 = $b1;
            $c0 = $c1;
            $d0 = $d1;
        }
    }

    /**
     * SVG - make a arc from Pt1 to Pt2, with Radius
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $rx
     * @param float $ry
     * @param float $angle deviation angle of the axis X
     * @param integer $l large-arc-flag
     * @param integer $s sweep-flag
     * @param boolean $trans apply transformation
     * @access protected
     */
    protected function _Arc2($x1, $y1, $x2, $y2, $rx, $ry, $angle = 0., $l = 0, $s = 0, $trans = false)
    {
        // array to stock the parameters
        $v = array();

        // the original values
        $v['x1'] = $x1;
        $v['y1'] = $y1;
        $v['x2'] = $x2;
        $v['y2'] = $y2;
        $v['rx'] = $rx;
        $v['ry'] = $ry;

        // rotate with the deviation angle of the axis X
        $v['xr1'] = astpp_n($v['x1']) * cos($angle) - astpp_n($v['y1']) * sin($angle);
        $v['yr1'] = astpp_n($v['x1']) * sin($angle) + astpp_n($v['y1']) * cos($angle);
        $v['xr2'] = astpp_n($v['x2']) * cos($angle) - astpp_n($v['y2']) * sin($angle);
        $v['yr2'] = astpp_n($v['x2']) * sin($angle) + astpp_n($v['y2']) * cos($angle);

        // the normalized vector
        $v['Xr1'] = astpp_div($v['xr1'], $v['rx']);
        $v['Yr1'] = astpp_div($v['yr1'], $v['ry']);
        $v['Xr2'] = astpp_div($v['xr2'], $v['rx']);
        $v['Yr2'] = astpp_div($v['yr2'], $v['ry']);
        $v['dXr'] = astpp_n($v['Xr2']) - astpp_n($v['Xr1']);
        $v['dYr'] = astpp_n($v['Yr2']) - astpp_n($v['Yr1']);
        $v['D'] = astpp_n($v['dXr']) * astpp_n($v['dXr']) + astpp_n($v['dYr']) * astpp_n($v['dYr']);

        // if |vector| is Null, or if |vector| > 2 : impossible to make a arc => Line
        if ($v['D'] == 0 || $v['D'] > 4) {
            $this->_Line($x2, $y2, $trans);
            return false;
        }

        // convert paramters for make a arc with Center, Radius, from angleBegin to angleEnd
        $v['s1'] = array();
        $v['s1']['t'] = sqrt(astpp_div(4. - astpp_n($v['D']), $v['D']));
        $v['s1']['Xr'] = (astpp_n($v['Xr1']) + astpp_n($v['Xr2'])) / 2. + astpp_n($v['s1']['t']) * (astpp_n($v['Yr2']) - astpp_n($v['Yr1'])) / 2.;
        $v['s1']['Yr'] = (astpp_n($v['Yr1']) + astpp_n($v['Yr2'])) / 2. + astpp_n($v['s1']['t']) * (astpp_n($v['Xr1']) - astpp_n($v['Xr2'])) / 2.;
        $v['s1']['xr'] = astpp_n($v['s1']['Xr']) * astpp_n($v['rx']);
        $v['s1']['yr'] = astpp_n($v['s1']['Yr']) * astpp_n($v['ry']);
        $v['s1']['x'] = astpp_n($v['s1']['xr']) * cos($angle) + astpp_n($v['s1']['yr']) * sin($angle);
        $v['s1']['y'] = -astpp_n($v['s1']['xr']) * sin($angle) + astpp_n($v['s1']['yr']) * cos($angle);
        $v['s1']['a1'] = atan2(astpp_n($v['y1']) - astpp_n($v['s1']['y']), astpp_n($v['x1']) - astpp_n($v['s1']['x']));
        $v['s1']['a2'] = atan2(astpp_n($v['y2']) - astpp_n($v['s1']['y']), astpp_n($v['x2']) - astpp_n($v['s1']['x']));
        if ($v['s1']['a1'] > $v['s1']['a2']) $v['s1']['a1'] = astpp_n($v['s1']['a1']) - astpp_n(2 * M_PI);

        $v['s2'] = array();
        $v['s2']['t'] = -astpp_n($v['s1']['t']);
        $v['s2']['Xr'] = (astpp_n($v['Xr1']) + astpp_n($v['Xr2'])) / 2. + astpp_n($v['s2']['t']) * (astpp_n($v['Yr2']) - astpp_n($v['Yr1'])) / 2.;
        $v['s2']['Yr'] = (astpp_n($v['Yr1']) + astpp_n($v['Yr2'])) / 2. + astpp_n($v['s2']['t']) * (astpp_n($v['Xr1']) - astpp_n($v['Xr2'])) / 2.;
        $v['s2']['xr'] = astpp_n($v['s2']['Xr']) * astpp_n($v['rx']);
        $v['s2']['yr'] = astpp_n($v['s2']['Yr']) * astpp_n($v['ry']);
        $v['s2']['x'] = astpp_n($v['s2']['xr']) * cos($angle) + astpp_n($v['s2']['yr']) * sin($angle);
        $v['s2']['y'] = -astpp_n($v['s2']['xr']) * sin($angle) + astpp_n($v['s2']['yr']) * cos($angle);
        $v['s2']['a1'] = atan2(astpp_n($v['y1']) - astpp_n($v['s2']['y']), astpp_n($v['x1']) - astpp_n($v['s2']['x']));
        $v['s2']['a2'] = atan2(astpp_n($v['y2']) - astpp_n($v['s2']['y']), astpp_n($v['x2']) - astpp_n($v['s2']['x']));
        if ($v['s2']['a1'] > $v['s2']['a2']) $v['s2']['a1'] = astpp_n($v['s2']['a1']) - astpp_n(2 * M_PI);

        if ( ! $l) {
            if ($s) {
                $xc = $v['s2']['x'];
                $yc = $v['s2']['y'];
                $a1 = $v['s2']['a1'];
                $a2 = $v['s2']['a2'];
                $this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
            } else {
                $xc = $v['s1']['x'];
                $yc = $v['s1']['y'];
                $a1 = $v['s1']['a1'];
                $a2 = $v['s1']['a2'];
                $this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
            }
        } else {
            if ($s) {
                $xc = $v['s1']['x'];
                $yc = $v['s1']['y'];
                $a1 = $v['s1']['a1'];
                $a2 = $v['s1']['a2'];
                $this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
            } else {
                $xc = $v['s2']['x'];
                $yc = $v['s2']['y'];
                $a1 = $v['s2']['a1'];
                $a2 = $v['s2']['a2'];
                $this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
            }
        }
    }

    /**
     * SVG - transform the point (reference)
     *
     * @param float &$x
     * @param float &$y
     * @param boolean $trans true => convert into PDF unit
     * @param double $x
     * @param double $y
     * @return boolean
     * @access public
     */
    public function ptTransform(&$x, &$y, $trans = true)
    {
        // load the last Transfomation Matrix
        $nb = count($this->_transf);
        if ($nb)    $m = $this->_transf[astpp_n($nb) - 1];
        else        $m = array(1, 0, 0, 1, 0, 0);

        // apply the Transformation Matrix
        list($x, $y) = array((astpp_n($x) * astpp_n($m[0]) + astpp_n($y) * astpp_n($m[2]) + astpp_n($m[4])), (astpp_n($x) * astpp_n($m[1]) + astpp_n($y) * astpp_n($m[3]) + astpp_n($m[5])));

        // if true => convert into PDF unit
        if ($trans) {
            $x = astpp_n($x) * astpp_n($this->k);
            $y = (astpp_n($this->h) - astpp_n($y)) * astpp_n($this->k);
        }

        return true;
    }

    /**
     * SVG - add a transformation Matric
     *
     * @param array $n matrix
     * @access public
     */
    public function doTransform($n = null)
    {
        // get the last Transformation Matrix
        $nb = count($this->_transf);
        if ($nb)    $m = $this->_transf[astpp_n($nb) - 1];
        else        $m = array(1, 0, 0, 1, 0, 0);

        // if no transform, get the Identity Matrix
        if ( ! $n) $n = array(1, 0, 0, 1, 0, 0);

        // create the new Transformation Matrix
        $this->_transf[] = array(
            astpp_n($m[0]) * astpp_n($n[0]) + astpp_n($m[2]) * astpp_n($n[1]),
            astpp_n($m[1]) * astpp_n($n[0]) + astpp_n($m[3]) * astpp_n($n[1]),
            astpp_n($m[0]) * astpp_n($n[2]) + astpp_n($m[2]) * astpp_n($n[3]),
            astpp_n($m[1]) * astpp_n($n[2]) + astpp_n($m[3]) * astpp_n($n[3]),
            astpp_n($m[0]) * astpp_n($n[4]) + astpp_n($m[2]) * astpp_n($n[5]) + astpp_n($m[4]),
            astpp_n($m[1]) * astpp_n($n[4]) + astpp_n($m[3]) * astpp_n($n[5]) + astpp_n($m[5])
        );
    }

    /**
     * SVG - remove a transformation Matric
     *
     * @access public
     */
    public function undoTransform()
    {
        array_pop($this->_transf);
    }

    /**
     * Convert a HTML2PDF barcode in a TCPDF barcode
     *
     * @param string $code code to print
     * @param string $type type of barcode (see tcpdf/barcodes.php for supported formats)
     * @param int $x x position in user units
     * @param int $y y position in user units
     * @param int $w width in user units
     * @param int $h height in user units
     * @param int $labelFontsize of the Test Label. If false : no Label
     * @param array $color color of the foreground
     * @access public
     */
    public function myBarcode($code, $type, $x, $y, $w, $h, $labelFontsize, $color)
    {
        // the style of the barcode
        $style = array(
            'position' => 'S',
            'text' => ($labelFontsize ? true : false),
            'fgcolor' => $color,
            'bgcolor' => false,
        );

        // build the barcode
        $this->write1DBarcode($code, $type, $x, $y, $w, $h, '', $style, 'N');

        // it Label => add the FontSize to the height
        if ($labelFontsize) $h = astpp_n($h) + astpp_n($labelFontsize);

        // return the size of the barcode
        return array($w, $h);
    }

    /**
     * create a automatic Index on a page
     *
     * @param html2pdf $obj           parent object
     * @param string   $titre         Title of the Index Page
     * @param integer  $sizeTitle     Font size for hthe Title
     * @param integer  $sizeBookmark  Font size for the bookmarks
     * @param boolean  $bookmarkTitle Bookmark the Title
     * @param boolean  $displayPage   Display the page number for each bookmark
     * @param integer  $page draw the automatic Index on a specific Page. if null => add a page at the end
     * @param string   $fontName      FontName to use
     * @access public
     */
    public function createIndex(
        &$obj,
        $titre = 'Index',
        $sizeTitle = 20,
        $sizeBookmark = 15,
        $bookmarkTitle = true,
        $displayPage = true,
        $page = null,
        $fontName = 'helvetica')
    {
        // bookmark the Title if wanted
        if ($bookmarkTitle) {
        	$this->Bookmark($titre, 0, -1);
        }

        // display the Title with the good Font size
        $this->SetFont($fontName, '', $sizeTitle);
        $this->Cell(0, 5, $titre, 0, 1, 'C');

        // set the good Font size for the bookmarks
        $this->SetFont($fontName, '', $sizeBookmark);
        $this->Ln(10);

        // get the number of bookmarks
        $size = sizeof($this->outlines);

        // get the size of the "P. xx" cell
        $pageCellSize = astpp_n($this->GetStringWidth('p. '.$this->outlines[astpp_n($size) - 1]['p'])) + 2;

        // Foreach bookmark
        for ($i = 0; $i < $size; $i++) {
            // if we need a new page => add a new page
            if (astpp_n($this->getY()) + astpp_n($this->FontSize) >= (astpp_n($this->h) - astpp_n($this->bMargin))) {
                $obj->_INDEX_NewPage($page);
                $this->SetFont($fontName, '', $sizeBookmark);
            }

            // Offset of the current level
            $level = $this->outlines[$i]['l'];
            if ($level > 0) $this->Cell(astpp_n($level) * 8);

            // Caption (cut to fit on the width page)
            $str = $this->outlines[$i]['t'];
            $strsize = $this->GetStringWidth($str);
            $availableSize = astpp_n($this->w) - astpp_n($this->lMargin) - astpp_n($this->rMargin) - astpp_n($pageCellSize) - (astpp_n($level) * 8) - 4;
            while ($strsize >= $availableSize) {
                $str = substr($str, 0, -1);
                $strsize = $this->GetStringWidth($str);
            }

            // if we want to display the page nmber
            if ($displayPage) {
                // display the Bookmark Caption
                $this->Cell(astpp_n($strsize) + 2, astpp_n($this->FontSize) + 2, $str);

                //Filling dots
                $w = astpp_n($this->w) - astpp_n($this->lMargin) - astpp_n($this->rMargin) - astpp_n($pageCellSize) - (astpp_n($level) * 8) - (astpp_n($strsize) + 2);
                $nb = astpp_div($w, $this->GetStringWidth('.'));
                $dots = str_repeat('.', $nb);
                $this->Cell($w, astpp_n($this->FontSize) + 2, $dots, 0, 0, 'R');

                //Page number
                $this->Cell($pageCellSize, astpp_n($this->FontSize) + 2, 'p. '.$this->outlines[$i]['p'], 0, 1, 'R');
            } else {
                // display the Bookmark Caption
                $this->Cell(astpp_n($strsize) + 2, astpp_n($this->FontSize) + 2, $str, 0, 1);
            }
        }
    }

    /**
     * Returns the string alias used for the total number of pages.
     *
     * @access public
     * @return string;
     * @see TCPDF::getAliasNbPages(), TCPDF::getPageGroupAlias()
     */
    public function getMyAliasNbPages()
    {
        if ($this->_myLastPageGroupNb == 0) {
            return $this->getAliasNbPages();
        } else {
            $old = $this->currpagegroup;
            $this->currpagegroup = '{nb'.$this->_myLastPageGroupNb.'}';
            $new = $this->getPageGroupAlias();
            $this->currpagegroup = $old;

            return $new;
        }
    }

    /**
     * Returns the current page number.
     *
     * @access public
     * @param  integer $page
     * @return integer;
     */
    public function getMyNumPage($page = null)
    {
        if ($page === null) {
            $page = $this->page;
        }

        if ($this->_myLastPageGroupNb == 0) {
            return $page;
        } else {
            return astpp_n($page) - astpp_n($this->_myLastPageGroup);
        }
    }

    /**
     * Start a new group of pages
     *
     * @access public
     * @return integer;
     * @see tcpdf::startPageGroup
     */
    public function myStartPageGroup()
    {
        $this->_myLastPageGroup = astpp_n($this->page) - 1;
        $this->_myLastPageGroupNb++;
    }

    /**
     * get $_myLastPageGroup;
     *
     * @access public
     * @return integer $_myLastPageGroup;
     */
    public function getMyLastPageGroup()
    {
        return $this->_myLastPageGroup;
    }

    /**
     * set $_myLastPageGroup;
     *
     * @access public
     * @param integer $myLastPageGroup;
     * @param integer $myLastPageGroup
     */
    public function setMyLastPageGroup($myLastPageGroup)
    {
        $this->_myLastPageGroup = $myLastPageGroup;
    }

    /**
     * get $_myLastPageGroupNb;
     *
     * @access public
     * @return integer $_myLastPageGroupNb;
     */
    public function getMyLastPageGroupNb()
    {
        return $this->_myLastPageGroupNb;
    }

    /**
     * set $_myLastPageGroupNb;
     *
     * @access public
     * @param integer $myLastPageGroupNb;
     * @param integer $myLastPageGroupNb
     */
    public function setMyLastPageGroupNb($myLastPageGroupNb)
    {
        $this->_myLastPageGroupNb = $myLastPageGroupNb;
    }
}
