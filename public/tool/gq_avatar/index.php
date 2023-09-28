<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>国庆头像生成 - 彩虹工具网</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="keywords" content="国庆头像生成,彩虹工具网,在线工具箱">
<meta name="description" content="国庆专属头像生成，告白祖国，快来一起换头像">
<style>
        a:link {
            color: #fff;
            text-decoration: none;
        }

        a:visited {
            color: #fff;
        }

        #export {
            display: none;
            margin: 0 auto;
            width: 250px;
            height: 250px;
            margin-top: 50px;
            margin-bottom: 50px
        }

        .operation-btns .o-btn1 {
            background-size: 11.6rem 4.325rem
        }

        .operation-btns .o-btn2 {
            background-size: 11.6rem 3.75rem
        }

        center {
            color: #fff;
        }
    </style>
<link rel="stylesheet" type="text/css" href="./static/css/jqery.css">
</head>
<body>
<div class="wrapper">
<img src="" alt="" class="img-load" style="width: 9.5rem; position: fixed; top: 0px; left: -9999px;">
<div class="operation-header">
<div class="h-title">
</div>
</div>
<div class="operation-box">
<a class="prev" onclick="changeHat()"></a>
<div class="operation-img">
<div class="cropper-content" id="content">
<canvas class="" id="cvs"></canvas>
</div>
</div>
<a class="next" onclick="changeHat()"></a>
</div>
<img id="export" alt="国庆专属头像" src="">
<div class="operation-btns">
<a class="o-btn1">
<input class="o-btn1" id="upload" type="file" onchange="viewer()" style="opacity: 0;">
</a>
<a class="o-btn2" onclick="exportFunc()" style="display: none;">
</a>
</div>
<div class="operation-done">
<img src="./logo.png" alt="" class="act-done-happy">
</div>
</div>
<div style="display: none">
<img id="img" src="" alt="">
<img class="hide" id="hat0" src="./static/img/hat1.png">
<img class="hide" id="hat1" src="./static/img/hat2.png">
<img class="hide" id="hat2" src="./static/img/hat3.png">
<img class="hide" id="hat3" src="./static/img/hat4.png">
<img class="hide" id="hat4" src="./static/img/hat5.png">
<img class="hide" id="hat5" src="./static/img/hat6.png">
<img class="hide" id="hat6" src="./static/img/hat7.png">
<img class="hide" id="hat7" src="./static/img/hat8.png">
<img class="hide" id="hat8" src="./static/img/hat9.png">
<img class="hide" id="hat9" src="./static/img/hat10.png">
<img class="hide" id="hat10" src="./static/img/hat11.png">
<img class="hide" id="hat11" src="./static/img/hat12.png">
<img class="hide" id="hat12" src="./static/img/hat13.png">
<img class="hide" id="hat13" src="./static/img/hat14.png">
<img class="hide" id="hat14" src="./static/img/hat15.png">
<img class="hide" id="hat15" src="./static/img/hat16.png">
<img class="hide" id="hat16" src="./static/img/hat17.png">
<img class="hide" id="hat17" src="./static/img/hat18.png">
<img class="hide" id="hat18" src="./static/img/hat19.png">
<img class="hide" id="hat19" src="./static/img/hat20.png">
<img class="hide" id="hat20" src="./static/img/hat21.png">
<img class="hide" id="hat21" src="./static/img/hat22.png">
<img class="hide" id="hat22" src="./static/img/hat23.png">
<img class="hide" id="hat23" src="./static/img/hat24.png">
<img class="hide" id="hat24" src="./static/img/hat25.png">
</div>
<script src="./static/js/fabric.min.js"></script>
<script>
    const cvs = document.getElementById("cvs");
    const ctx = cvs.getContext("2d");
    const exportImage = document.getElementById("export");
    const img = document.getElementById("img");
    let hat = "hat1";
    let canvasFabric;
    let hatInstance;
    //var screenWidth = window.screen.width < 500 ? window.screen.width : 300;
    const scrollHeight = document.getElementById("content").scrollHeight;

    function viewer() {
        const file = document.getElementById("upload").files[0];
        const reader = new FileReader;
        if (file) {
            reader.readAsDataURL(file);
            reader.onload = function (e) {
                img.src = reader.result;
                img.onload = function () {
                    img2Cvs(img)
                }
            }
        } else {
            img.src = ""
        }
    }

    function img2Cvs(img) {
        cvs.width = img.width;
        cvs.height = img.height;
        cvs.style.display = "block";
        canvasFabric = new fabric.Canvas("cvs", {
            width: scrollHeight,
            height: scrollHeight,
            backgroundImage: new fabric.Image(img, {
                scaleX: scrollHeight / img.width,
                scaleY: scrollHeight / img.height
            })
        });
        changeHat();

        document.getElementsByClassName("o-btn1")[0].style.display = "none";
        document.getElementsByClassName("o-btn2")[0].style.display = "block";
        //document.getElementById("tip").style.opacity = 1
    }

    function changeHat() {
        document.getElementById(hat).style.display = "none";
        const hats = document.getElementsByClassName("hide");
        hat = "hat" + (+hat.replace("hat", "") + 1) % hats.length ;
        const hatImage = document.getElementById(hat);
        hatImage.style.display = "block";
        if (hatInstance) {
            canvasFabric.remove(hatInstance)
        }
        hatInstance = new fabric.Image(hatImage, {
            top: 0,
            left: 0,
            scaleX: scrollHeight / hatImage.width,
            scaleY: scrollHeight / hatImage.height,
            cornerColor: "#0b3a42",
            cornerStrokeColor: "#fff",
            cornerStyle: "circle",
            transparentCorners: false,
            rotatingPointOffset: 30
        });
        hatInstance.setControlVisible("bl", false);
        hatInstance.setControlVisible("tr", false);
        hatInstance.setControlVisible("tl", false);
        hatInstance.setControlVisible("mr", false);
        hatInstance.setControlVisible("mt", false);
        canvasFabric.add(hatInstance)
    }

    function exportFunc() {
        document.getElementsByClassName("operation-box")[0].style.display = "none";
        document.getElementsByClassName("operation-btns")[0].style.display = "none";

        /*document.getElementById("exportBtn").style.display = "none";
        document.getElementById("tip").innerHTML = "长按图片保存或分享";
        document.getElementById("change").style.display = "none";*/
        cvs.style.display = "none";
        exportImage.style.display = "block";
        exportImage.src = canvasFabric.toDataURL({
            width: scrollHeight,
            height: scrollHeight
        });
        alert('长按图片保存或分享');
    }


</script>
</body>
</html>