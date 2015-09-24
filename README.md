These are Nintendo 3DS system web-browser webkit exploits for Old3DS and New3DS.

This requires the following repo: https://github.com/yellows8/3ds_browserhax_common See that repo for usage info as well.  

These are webkit exploits, so you may have to retry them multiple times before they work correctly without crashing.

Two exploits are contained here:
* Old3DS: 3dsbrowserhax_webkit_r158724.php, aka "sliderhax". All system-versions <=10.1.0-27 are supported, as of when this repo was released. This isn't actually fixed for the New3DS browser as of 10.1.0-27, but there's no known way to even have a crash trigger for it which actually works right. To trigger it, wait for the page to fully load. Then ideally zoom in all the way, so that the slider is displayed as large as possible. Then touch the far right of the slider at the exact location where the slider ends, within the slider bar(the location you touch might(?) be related to how reliable the exploit is).
* New3DS: 3dswebkithax_removewinframe.php, supported on all system-versions below 9.9.0-26(or more specifically <{X.X.X-26}). The vuln used here was fixed for the New3DS browser with 9.9.0-26, but on Old3DS it's still not fixed as of 10.1.0-27. This is based on a certain PoC, see the source for details on that. No user-input is needed to trigger this besides starting the page-load. The actual exploit after the heap-spray takes a while to trigger, since the heap-spray takes a while. Note that this is *very* unreliable.

See the following for a hosted version of these: http://yls8.mtheall.com/3dsbrowserhax.php

