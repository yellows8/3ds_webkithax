These are Nintendo 3DS system web-browser webkit exploits for Old3DS and New3DS.

This requires the following repo: https://github.com/yellows8/3ds_browserhax_common See that repo for usage info as well.  

These are webkit exploits, so you may have to retry them multiple times before they work correctly without crashing.

Three exploits are contained here, two are implemented only for Old3DS, and the other is only implemented for New3DS:
* Old3DS: 3dsbrowserhax_webkit_r158724.php, aka "sliderhax". All system-versions <=10.1.0-27(minus the first version of the browser) are supported, as of when this repo was released. This vuln was fixed with 10.2.0-28 for the Old3DS and New3DS browser. To trigger it, wait for the page to fully load. Then ideally zoom in all the way, so that the slider is displayed as large as possible. Then touch the far right of the slider at the exact location where the slider ends, within the slider bar(the location you touch might(?) be related to how reliable the exploit is).
* New3DS: 3dswebkithax_removewinframe.php, supported on all system-versions below 9.9.0-26(or more specifically <{X.X.X-26}). The vuln used here was fixed for the New3DS browser with 9.9.0-26. On Old3DS this was fixed with 9.5.0-23(or more specifically >={X.X.X-23}). No user-input is needed to trigger this besides starting the page-load. The actual exploit after the heap-spray takes a while to trigger, since the heap-spray takes a while. Note that this is *very* unreliable.
* Old3DS: 3dsbrowserhax_webkit_r106972.php, aka "spider28hax". Only supported on system-versions 10.2.0-28..10.5.0-30(aka X.X.X-28 - X.X.X-30) and KOR+CHN 4.2.0-9. The 9.9.0-26(X.X.X-26) CHN/TWN/KOR browser is also supported. X.X.X-28 was the latest NUP-version available at the time of exploit release, hence "spider28hax". The exploit will automatically trigger while the page is loading. This seems to be much more stable than sliderhax.
* Old3DS: spider31hax. Only supported on system-versions 10.6.0-31..11.0.0-33(aka, only X.X.X-31 - X.X.X-33), and 2.0.0-2..10.1.0-27(X.X.X-2 - X.X.X-27). The testcase this is based on was found to affect Old3DS browser by [MrRean](https://www.youtube.com/watch?v=6iU9xFXDO2w), after trying ~200 testcases in less than 24h. This was found + exploited on June 22, 2016.

* 3dsbrowserhax_webkit_r158724.php in the initial form that got control over the object-data used in the use-after-free, is originally from January 2014. The vuln used here was discovered to affect Old3DS web-browser by ichfly.
* 3dswebkithax_removewinframe.php: This is based on a certain PoC, see the source for details on that. This was implemented in March 2015, soon after the time the pastebin for the PoC was created.
* 3dsbrowserhax_webkit_r106972.php: @Slashmolder(aka sm) for the PoC this exploit is based on(which was identical to the WebKit testcase except with a modified gc() function). That PoC was shared on December 21, 2015. It was successfully exploited on December 23, 2015. If a good crash-trigger PoC/testcase wouldn't have been shared around that timeframe, it's possible that no Old3DS browserhax would have been released on the planned release date.

See the following for a hosted version of these: https://yls8.mtheall.com/3dsbrowserhax.php

