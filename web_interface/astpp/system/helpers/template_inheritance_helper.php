<?php
	/**
	 * Django - like template inheritance emulation for PHP
	 *
	 * @author Daniel Dornhardt <daniel AT dornhardt.com>
	 *
	 * I liked djangos template inheritance - system, so I decided to create
	 * something similar for PHP.
	 *
	 * It's not pretty, it's not well tested (yet), but from my intuition it should be
	 * simple to use and pretty fast.
	 *
	 * @copyright Copyright (c) 2008 by Daniel Dornhardt
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 *
	 * Permission is hereby granted, free of charge, to any person
	 * obtaining a copy of this software and associated documentation
	 * files (the "Software"), to deal in the Software without
	 * restriction, including without limitation the rights to use,
	 * copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the
	 * Software is furnished to do so, subject to the following
	 * conditions:
	 *
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	 * OTHER DEALINGS IN THE SOFTWARE.
	 *
	 */

/*

CONFIGURATION:
If you are using Code Igniter, you shouldn't have to do anything. If not, you'll have to define a 'TI_VIEWS_DIR' constant, like define ('TI_VIEWS_DIR', 'views/'); which should be a path where PHP can find your view files. You can make it relative to the current PHP working dir or absolute, it shouldn't matter. If you don't do this, you'll have add the path to your files to your extend() calls. 
Then just include this file somehow. Cod Igniter users can drop it into their 'helpers' - dir and make sure that it gets loaded (via autoload or manually).

QUICK TUTORIAL:
Imagine you have a website with the same basic structure in every page. Something like this: 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        Dan's Music Store
    </title>
    <link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
</head>
<body id="body">
    <div id="container">
        <!-- Content goes here -->
    </div> <!-- container -->
</body>
</html>

Then you might have different subpages which actually use the same basic structure. Now you can use PHP to include separate files for every area of that template, but I always felt like this was a suboptimal solution.
So the idea is to put some kind of markers into the base template which can be extended from child templates. We'll call it "main_template.php". An example could look like this:

main_template.php:
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>

        <? start_block_marker('title') ?>
            Dan's Music Store
        <? end_block_marker() ?>

    </title>

    <? start_block_marker('extra_head') ?>
        <link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
    <? end_block_marker() ?>

</head>
<body id="body">
    <div id="container">

        <? start_block_marker('content') ?>
            <h1>Welcome to the Dan's Music Store</h1>
            <p>Instruments from all over the world</p>
        <? end_block_marker() ?>

    </div> <!-- container -->
</body>
</html>

If you include just this, essentially you would get the same output as in the first example, because no special block content has been assigned yet.
To use this file as a base template for different content, you have to use the extend() function and start overriding the blocks of the base template. Let's call this one "guitars.php". It will be a section template which includes some extra content for the guitar section of our music store.

guitars.php:
<? extend('main_template.php') ?>

    <? startblock('title') ?>
        <?= get_extended_block() ?>
        - Guitars
    <? endblock() ?>

    <? startblock('extra_head') ?>
        <?= get_extended_block() ?>
        <link rel="stylesheet" href="css/guitars.css" type="text/css" media="all" />
    <? endblock() ?>

    <? startblock('content') ?>
        <h2>Look around!</h2>
        <p>Such a fine selection of Guitars!</p>
    <? endblock() ?>

<? end_extend() ?>


With extend('filename'), you tell this template which base template it should extend. You need to wrap this file up with a call to <? end_extend() ?> to make the magic work.
You can call get_extended_block() to inherit / receive the content of the parent block, which is useful for adding additional data to the base template.
In this example it's used to add a second part to the <title> - tag and to add an additional stylesheet.
Now we would include "guitars.php" instead of "main_template.php".

The output would look like this*:
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>

        Dan's Music Store
        - Guitars

    </title>

                <link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
            <link rel="stylesheet" href="css/guitars.css" type="text/css" media="all" />

</head>

<body id="body">
    <div id="container">

                <h2>Look around!</h2>
        <p>Such a fine selection of Guitars!</p>

    </div> <!-- container -->
</body>
</html>
*I fixed the indentation a little

So now we have the extra content for the title and the head. The body has been overwritten by the content of "guitars.php".
Then we need a page where we display the specific guitars. We'll create a file
'destroyer_guitar.php'. It will extend the "guitar.php" - file. Of course the contents of this file will probably be dynamic in your case and not only a file for a specific guitar, but this is an example, mkaaaaay?

destroyer_guitar.php:
<? extend('guitars.php') ?>

    <? startblock('title'); ?>
        <? get_extended_block() ?>
        - Destroyer ZX80
    <? endblock() ?>

    <? startblock('content') ?>
        <h1>Destroyer ZX80</h1>
        <p>A most excellent heavy metal Axe.</p>
        <p>Available in the following sizes:</p>
        <ul>
            <li>Small</li>
            <li>Large</li>
            <li>Troll</li>
        </ul>
    <? endblock() ?>

<? end_extend() ?>

So here we once more replace the content with something different. The 'content' - data will overwrite the content from "guitars.php" and "main_content.php". If we wanted to preserve it, we could have left the content block out or could have inserted <? get_extended_block() ?> somewhere.
Then the content from "guitars.php" would appear in that spot in the main template.
We did this for the 'title' - block, which inherits the data from the base templates.
If we would include "destroyer_guitar.php" now instead of "main_template.php" or "guitars.php", the output would look like this:

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>

        Dan's Music Store
        - Guitars
        - Destroyer ZX80

    </title>

                <link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
            <link rel="stylesheet" href="css/guitars.css" type="text/css" media="all" />

</head>
<body id="body">

    <div id="container">

                <h1>Destroyer ZX80</h1>
        <p>A most excellent heavy metal Axe.</p>
        <p>Available in the following sizes:</p>
        <ul>
            <li>Small</li>

            <li>Large</li>
            <li>Troll</li>
        </ul>

    </div> <!-- container -->
</body>
</html>

... now isn't that just beautiful? :)

CAUTION:
EVERY block within the inheritance hierarchy will be executed ONCE, whether its contents will be used or not.

That means that if some base template block contains anything, it
will always be executed, whether its output will be used or not.

If this calculation would be too expensive, there is a function you can call to check if the current block's content is required or if it only will be overridden by the child templates anyways. Call block_rendering_neccessary() to find out if the block you're currently crafting will be rendered to avoid unneccessary computation. Like this:

<? startblock('weather') ?>
    <? if (block_rendering_neccessary()): ?>
        <?= simulate_global_warming_effects() ?>
    <? endif; ?>
<? endblock() ?>

You could also check django's template inheritance documentation for another example. Django's template inheritance was my inspiration - actually I pretty much copied its functionality. I hope they don't mind.

http://www.djangoproject.com/documentation/templates/#template-inheritance

That's it for now. If you have questions, ideas or problems, please write me at daniel AT dornhardt.com.
*/
	if (! defined('TI_VIEWS_DIR') ) {
		if (defined('APPPATH')) {
			define('TI_VIEWS_DIR', APPPATH.'views/');
		} else {
			define('TI_VIEWS_DIR', '');
		}
	}
//    echo TI_VIEWS_DIR;
	define('TI_MARKER_EXTEND_BLOCK_HERE', '{{{[[[{[{[{[INSERT_BASE_DATA_HERE]}]}]}]]]}}}');

	/**
	 * Gather output from extended templates
	 *
	 * array('blockname' => 'blockContent', ...)
	 */
	$GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'] = array();

	/**
	 * keep the name of the last marker
	 */
	$GLOBALS['TI_CURRENT_BLOCKNAME'] = '';

	/**
	 * keep the name of the file we are extending right now
	 */
	$GLOBALS['TI_CURRENT_BASE_TEMPLATE'] = '';

	/**
	 * Called to extend some parts of the base template $filename with content from this file
	 *
	 * @param string $filename - filename of the base template
	 */
	function extend($filename) {
//    	echo "externd";
		$GLOBALS['TI_CURRENT_BASE_TEMPLATE'] = $filename;
//        exit();
	}

	/**
	 * End the extension process for this file. Needs to be called after all blocks
	 * have been set.
	 *
	 */
	function end_extend() {
		//echo 'tttttttttttt';
		if (isset($GLOBALS['CI'])) {
			$GLOBALS['CI']->load->view($GLOBALS['TI_CURRENT_BASE_TEMPLATE']);
		}
		else {
			include realpath( TI_VIEWS_DIR . $GLOBALS['TI_CURRENT_BASE_TEMPLATE']);
		}
	}

	/**
	 * Start a top-level block. Its contents can be replaced from within child templates.
	 *
	 * @param string $blockname
	 */
	function start_block_marker($blockname) {
		// remember block name for end_block_marker()
		$GLOBALS['TI_CURRENT_BLOCKNAME'] = $blockname;
		// start caching this blocks output
		ob_start();
	}

	/**
	 * End a top-level block. Its contents can be replaced from within child templates.
	 *
	 */
	function end_block_marker() {
		// get block content
		$thisBlocksContent = ob_get_clean();
		// check if we got data for this block from child templates
		if (astpp_array_key_exists($GLOBALS['TI_CURRENT_BLOCKNAME'], $GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'])) {
			// if yes, use that data instead of what's in this block
			// - except we got a marker that the child wants us to include this blocks content
			// in some places. If so, place our data into those spots.
			$thisBlocksContent = str_replace(
				TI_MARKER_EXTEND_BLOCK_HERE,
				$thisBlocksContent,
				$GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'][$GLOBALS['TI_CURRENT_BLOCKNAME']]
			);
		}
		// output result
		echo $thisBlocksContent;
	}


	/**
	 * Mark the start of a block as content to be embedded into the base template.
	 *
	 * @param string $blockname
	 */
	function startblock($blockname) {
		// remember block name for endblock()
		$GLOBALS['TI_CURRENT_BLOCKNAME'] = $blockname;
		// start caching this blocks output
		ob_start();
	}

	/**
	 * Mark the end of a block as content to be embedded into the base template.
	 */
	function endblock() {
		// get block content
		$thisBlocksContent = ob_get_clean();
		// check if we got data for this block from child templates
		if (astpp_array_key_exists($GLOBALS['TI_CURRENT_BLOCKNAME'], $GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'])) {
			// check if we got a marker that the child wants us to include this blocks content
			// in some places. If so, place our data into those spots.
			$thisBlocksContent = str_replace(
				TI_MARKER_EXTEND_BLOCK_HERE,
				$thisBlocksContent,
				$GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'][$GLOBALS['TI_CURRENT_BLOCKNAME']]
			);
		}
		// save this blocks content for use in templates higher up in the hierarchy
		$GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'][$GLOBALS['TI_CURRENT_BLOCKNAME']] = $thisBlocksContent;
	}

	/**
	 * Insert a marker at this position to add the content from the base templates to this block.
	 */
	function get_extended_block() {
		echo TI_MARKER_EXTEND_BLOCK_HERE;
	}

	/**
	 * Check if this block's contents will be needed.
	 * True if no child did override this block or a child needs this block's content
	 *
	 * @return bool
	 */
	function block_rendering_neccessary() {
		// check if no child did override this block
		if (!astpp_array_key_exists($GLOBALS['TI_CURRENT_BLOCKNAME'], $GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'] )) {
			return true;
		}
		// check if there is an extension marker in the child blocks data. If so, the
		// rendering of this block is required
		if (false === strpos($GLOBALS['TI_EXTENDED_BASE_TEMPLATE_DATA'][$GLOBALS['TI_CURRENT_BLOCKNAME']], TI_MARKER_EXTEND_BLOCK_HERE)) {
			return false;
		} else {
			return true;
		}
	}
?>  