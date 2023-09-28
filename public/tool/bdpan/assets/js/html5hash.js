$().ready(function () {
    getUnique = function () {
        var uniquecnt = 0;

        function getUnique() {
            return (uniquecnt++);
        }

        return getUnique;
    }();

    function decimalToHexString(number) {
        if (number < 0) {
            number = 0xFFFFFFFF + number + 1;
        }

        return number;
    }

    function digits(number, dig) {
        var shift = Math.pow(10, dig);
        return Math.floor(number * shift) / shift;
    }

    function escapeHtml(text) {
        return $('<div/>').text(text).html();
    }

    function swapendian32(val) {
        return (((val & 0xFF) << 24)
           | ((val & 0xFF00) << 8)
           | ((val >> 8) & 0xFF00)
           | ((val >> 24) & 0xFF)) >>> 0;

    }
    function arrayBufferToWordArray(arrayBuffer) {
        var fullWords = Math.floor(arrayBuffer.byteLength / 4);
        var bytesLeft = arrayBuffer.byteLength % 4;

        var u32 = new Uint32Array(arrayBuffer, 0, fullWords);
        var u8 = new Uint8Array(arrayBuffer);

        var cp = [];
        for (var i = 0; i < fullWords; ++i) {
            cp.push(swapendian32(u32[i]));
        }

        if (bytesLeft) {
            var pad = 0;
            for (var i = bytesLeft; i > 0; --i) {
                pad = pad << 8;
                pad += u8[u8.byteLength - i];
            }

            for (var i = 0; i < 4 - bytesLeft; ++i) {
                pad = pad << 8;
            }

            cp.push(pad);
        }

        return CryptoJS.lib.WordArray.create(cp, arrayBuffer.byteLength);
    };

    function bytes2si(bytes, outputdigits) {
        if (bytes < 1024) { // Bytes
            return digits(bytes, outputdigits) + " b";
        }
        else if (bytes < 1048576) { // KiB
            return digits(bytes / 1024, outputdigits) + " KiB";
        }
        
        return digits(bytes / 1048576, outputdigits) + " MiB";
    }

    function bytes2si2(bytes1, bytes2, outputdigits) {
        var big = Math.max(bytes1, bytes2);

        if (big < 1024) { // Bytes
            return bytes1 + "/" + bytes2 + " b";
        }
        else if (big < 1048576) { // KiB
            return digits(bytes1 / 1024, outputdigits) + "/" +
                digits(bytes2 / 1024, outputdigits) + " KiB";
        }

        return digits(bytes1 / 1048576, outputdigits) + "/" +
            digits(bytes2 / 1048576, outputdigits) + " MiB";
    }

    function progressiveRead(file, work, done) {
        var chunkSize = 262144; // 256KiB at a time
        var pos = 0;
        var reader = new FileReader();

        function progressiveReadNext() {
            var end = Math.min(pos + chunkSize, file.size);

            reader.onload = function (e) {
                pos = end;
                work(e.target.result, pos, file);
                if (pos < file.size) {
                    progressiveReadNext();
                }
                else {
                    // Done
                    done(file);
                }
            }

            if (file.slice) {
                var blob = file.slice(pos, end);
            }
            else if (file.webkitSlice) {
                var blob = file.webkitSlice(pos, end);
            }
            reader.readAsArrayBuffer(blob);
        }

        progressiveReadNext();
    };

    var algorithms = [
        { name: "MD5", type: CryptoJS.algo.MD5 }
    ];
    function selectFile(f) {
        (function () {
                var start = (new Date).getTime();
                var lastprogress = 0;
				
				var contentMd5 = CryptoJS.algo.MD5.create();
				var sliceMd5 = CryptoJS.algo.MD5.create();
				var slice = 0;

                var crc32intermediate = 0;
                var uid = "filehash" + getUnique();
                $("#showTable").append('<tr><td class="hash_file_info" id="'+uid+'"></td></tr>');
                progressiveRead(f,
                function (data, pos, file) {
                    // Work
                    // Easiest way to get this up and running ;-) Obvious optimization potential there.
                    var wordArray = arrayBufferToWordArray(data);

                    contentMd5.update(wordArray);
					if(slice==0){
						sliceMd5.update(wordArray);
						slice = 1;
					}

                    crc32intermediate = crc32(new Uint8Array(data), crc32intermediate);

                    // Update progress display
                    var progress = Math.floor((pos / file.size) * 100);
                    if (progress > lastprogress) {
                        $(file.previewElement).find('.dz-progress .dz-upload').css('width', progress + '%');

                        var took = ((new Date).getTime() - start) / 1000;
                        $('#' + uid).html('<font color="blue">' + file.name +'</font>（'+ bytes2si2(pos, file.size, 2)+'）| 耗时: ' + digits(took, 2) + 's @ ' + bytes2si(pos / took, 2) + '/s<br/><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="' + progress + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + progress + '%">' + progress + '%</div>')
                        lastprogress = progress;
                    }
                },
                function (file) {
                    // Done
                    $(file.previewElement).removeClass('dz-progressing');
                    $(file.previewElement).addClass('dz-success dz-complete');
					$('#' + uid).addClass('hashlink');

                    var took = ((new Date).getTime() - start) / 1000;

                    var results = 'bdpan://|' + file.name + '|' + contentMd5.finalize() + '|' + sliceMd5.finalize() + '|' + decimalToHexString(crc32intermediate) + '|' + file.size + '|/';

					if(localStorage.getItem('historylink')){
						localStorage.setItem('historylink', localStorage.getItem('historylink')+'*'+results);
					}else{
						localStorage.setItem('historylink', results);
					}

                    $("#" + uid).html(results);
                });
            })();
    }

    function compatible() {
        try {
            // Check for FileApi
            if (typeof FileReader == "undefined") return false;

            // Check for Blob and slice api
            if (typeof Blob == "undefined") return false;
            var blob = new Blob();
            if (!blob.slice && !blob.webkitSlice) return false;

            // Check for Drag-and-drop
            if (!('draggable' in document.createElement('span'))) return false;
        } catch (e) {
            return false;
        }
        return true;
    }

    if (!compatible()) {
       alert('请更换高级浏览器，以支持本工具功能！');
    }
    Dropzone.autoDiscover = false;
    var hashFile = new Dropzone("#hash_file");
    hashFile.options.maxFilesize = 204800; // 不限制大小
    hashFile.options.autoProcessQueue = false;
    hashFile.on("addedfile", function(file) {
        selectFile(file);
    });
});