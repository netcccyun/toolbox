function decompressPublicKey(compressedKey) {
    // 获取压缩标志和X坐标
    var flag = compressedKey.slice(0, 2);
    var x = compressedKey.slice(2);
    
    // 定义常量
    var p = BigInt('0xFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFF', 16);
    var a = BigInt('0xFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFC', 16);
    var b = BigInt('0x28E9FA9E9D9F5E344D5A9E4BCF6509A7F39789F515AB8F92DDBCBD414D940E93', 16);
    
    // 计算Y坐标
    // y2 = x3 + ax + b
    var xBigInt = BigInt('0x' + x);
    var alpha = (xBigInt ** 3n) % p;
    var beta = (a * xBigInt + b) % p;
    var y2 = (alpha + beta) % p;
    var y = modularExponentiation(y2, (p + 1n) / 4n, p);
    
    // y2 的值开方有两个值，根据奇偶判定取哪一个
    if (flag === "02") {
        // 如果压缩标志为“02”，则Y坐标为偶数
        if (y % 2n === 0n) {
            return "04" + x + y.toString(16).padStart(64, '0');
        } else {
            y = p - y;
            return "04" + x + y.toString(16).padStart(64, '0');
        }
    } else if (flag === "03") {
        // 如果压缩标志为“03”，则Y坐标为奇数
        if (y % 2n === 1n) {
            return "04" + x + y.toString(16).padStart(64, '0');
        } else {
            y = p - y;
            return "04" + x + y.toString(16).padStart(64, '0');
        }
    } else {
        return null;
    }
}
function modularExponentiation(base, exponent, modulus) {
    var result = 1n;
    base = base % modulus;
    while (exponent > 0n) {
        if (exponent % 2n === 1n) {
            result = (result * base) % modulus;
        }
        base = (base * base) % modulus;
        exponent = exponent / 2n;
    }
    return result;
}
function hexToBase64(hex) {
    var bytes = [];
    for (var i = 0; i < hex.length-1; i += 2) {
        bytes.push(parseInt(hex.substr(i, 2), 16));
    }
    var byteArray = new Uint8Array(bytes);
    return btoa(String.fromCharCode.apply(null, byteArray));
}
function base64ToHex(base64) {
    var binaryString = atob(base64);
    var byteArray = new Uint8Array(binaryString.length);
    for (var i = 0; i < binaryString.length; i++) {
        byteArray[i] = binaryString.charCodeAt(i);
    }
    var hexArray = Array.prototype.map.call(byteArray, function(byte) {
        return ('0' + byte.toString(16)).slice(-2);
    });
    return hexArray.join('');
}