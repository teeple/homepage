<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> <!-- Transitional to be compliant to W3C specs in spite of <a>:target attribute -->

<!-- Easy Image plugin 1.0 (c) FFh Lab / Eric Lequien for TinyMCE 3.x+ (c) Moxiecode Systems AB -->

<head>
	<meta http-equiv="Content-Type" content="text/html" />
	<title>{#ezimage_dlg.title}</title>
    <?php
	$file = dirname(__FILE__);
	$file = substr($file, 0, stripos($file, "wp-content") );
	require( $file . "/wp-load.php");
	$url = includes_url();
	echo '<script type="text/javascript" src="'.$url.'js/tinymce/tiny_mce_popup.js'.'"></script>';
	echo '<script type="text/javascript" src="'.$url.'js/tinymce/utils/mctabs.js'.'"></script>';
	echo '<script type="text/javascript" src="'.$url.'js/tinymce/utils/form_utils.js'.'"></script>';
	?>
    <!--
	<script type="text/javascript" src="../../tinymce/tiny_mce_popup.js"></script>
    -->
	<script type="text/javascript" src="js/ezimage.js"></script>
	<link href="css/ezimage.css" rel="stylesheet" type="text/css" />
</head>
<body>

<form action="#">
	<p id="aboutcmd" onclick="display('aboutbox','block');">{#ezimage_dlg.about}</p>
	<div id="aboutbox" onclick="display(this.id,'none');">
	EasyImage plugin 1.0 (c) <a href="http://ffh-lab.com/" target="_blank">FFh Lab</a> / Eric Lequien, 2009-2012
	<br />for TinyMCE 3.x (c) <a href="http://tinymce.moxiecode.com/" target="_blank">Moxiecode Systems AB</a>, 2003-2012
	<hr />{#ezimage_dlg.legal}</div>

	<p>{#ezimage_dlg.src} : <input id="src" name="src" type="text" class="text" onchange="showPrev();" /></p>

	<p>{#ezimage_dlg.txt} : <input id="txt" name="txt" type="text" class="text" onchange="showPrev();" />
		<input id="txtastitle" name="opttxt" type="radio" value="title" /><label for="txtastitle">{#ezimage_dlg.txtastitle}</label>
		<input checked="checked" id="txtascaption" name="opttxt" type="radio" value="caption" /><label for"txtascaption">{#ezimage_dlg.txtascaption}</label>
	<br /><span id="altlabel">{#ezimage_dlg.alt} : </span>
		<input id="alt" name="alt" type="text" class="text" onchange="showPrev();" style="display: none" />
		<input checked="checked" id="altastxt" name="altastxt" type="checkbox" value="altastxt" 
			onclick="adjustAltDisplay(); showPrev();" /><label for="altastxt">{#ezimage_dlg.altastxt}</label></p>

	<p>{#ezimage_dlg.width} : <input id="width" name="width" type="text" class="text" maxlength="3" onchange="showPrev();" />
		&nbsp;&nbsp;&nbsp;{#ezimage_dlg.popup}
		&nbsp;:&nbsp;<input id="popimg" name="popimg" type="checkbox" value="popimg" onclick="showPrev();" /></p>

	<table>
	<tr>
		<td>
			<table id="position">
			<tr><td rowspan="3">{#ezimage_dlg.align} :</td>
				<td><input checked="checked" id="posleft" name="optpos" type="radio" value="left" onclick="setAutomargins(document.forms[0],true);showPrev();" /><label for="pos">{#ezimage_dlg.left}</label></td></tr>
			<tr><td><input id="poscenter" name="optpos" type="radio" value="center" onclick="setAutomargins(document.forms[0],true);showPrev();" /><label for="poscenter">{#ezimage_dlg.center}</label></td></tr>
			<tr><td><input id="posright" name="optpos" type="radio" value="right" onclick="setAutomargins(document.forms[0],true);showPrev();" /><label for="posright">{#ezimage_dlg.right}</label></td></tr>
			</table>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table id="margins">
			<tr><td rowspan="3">{#ezimage_dlg.margin} :</td>
				<td></td>
				<td><input id="margtop" name="margtop" type="text" class="text" maxlength="4" size="3" onchange="showPrev();" /></td>
				<td></td></tr>
			<tr><td><input id="margleft" name="margleft" type="text" class="text" maxlength="4" size="3" onchange="showPrev();" /></td>
				<td><input id="margaxe" name="margaxe" type="text" size="3" readonly="readonly" /></td>
				<td><input id="margright" name="margright" type="text" class="text" maxlength="4" size="3"
							 onchange="showPrev();" /></td></tr>
			<tr><td></td>
				<td><input id="margbottom" name="margbottom" type="text" class="text" maxlength="4" size="3"
							 onchange="showPrev();" /></td>
				<td></td></tr>
			</table>
		</td>
		<td><table id="automargins">
			<tr><td>{#ezimage_dlg.automargin} :</td>
				<td><input id="automarg" name="automarg" type="checkbox" value="automarg" 
					onclick="setAutomargins(document.forms[0],this.checked);showPrev();" /></td></tr>
			</table>
		</td>
	</tr>
	</table>

	<div id="prevlabel">{#ezimage_dlg.preview}</div><div id="pophlp">{#ezimage_dlg.pophlp}</div>
	<div id="prev"></div>
	<div id="prevmsg">{#ezimage_dlg.prevmsg}</div>

	<div id="org"></div>

	<div class="mceActionPanel">
		<div class="fleft">
		<input type="button" id="insert" name="insert" value="{#insert}" onclick="ezimageDialog.insert();" />
 		<input type="button" id="prevcmd" name="prevcmd" value="{#ezimage_dlg.prevcmd}" onclick="showPrev();" />

		</div>
		<div class="fright">
		<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" /></div>
	</div>
</form>
</body>
</html>