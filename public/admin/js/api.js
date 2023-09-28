const $ = layui.jquery;

const request = (url, data, params) => {

    return new Promise((resolve, reject) => {
        contentType = 'application/x-www-form-urlencoded';
        if (data !== null && data !== undefined && (data.length > 0 || Object.keys(data).length > 0) && typeof data == 'object') {
            data = JSON.stringify(data)
            contentType = 'application/json'
        }
        $.ajax({
            url: url,
            type: params && params.type || 'post',
            dataType: params && params.dataType || 'JSON',
            contentType: contentType,
            data: data,
            success: function (res) {
                resolve(res)
            },
            error: function (res) {
                reject(res.message)
            }
        });
    });

}
const httpGet = (url) => {
    return request(url, {}, {
        type: 'GET',
    }).then(res => {
        if (res.status !== 'ok') {
            $message.error(res.message);
        }
        return res
    })
}
const httpDelete = (url, data) => {
    return request(url, data, {
        type: 'DELETE',
    }).then(res => {
        if (res.status !== 'ok') {
            $message.error(res.message);
        }
        return res
    })
}
const httpPost = (url, data) => {
    return request(url, data, {
        type: 'POST',
    }).then(res => {
        if (res.status !== 'ok') {
            $message.error(res.message);
        }
        return res
    })
}
const httpPut = (url, data) => {
    return request(url, data, {
        type: 'PUT',
    }).then(res => {
        if (res.status !== 'ok') {
            $message.error(res.message);
        }
        return res
    })
}
const httpPatch = (url, data) => {
    return request(url, data, {
        type: 'PATCH',
    }).then(res => {
        if (res.status !== 'ok') {
            $message.error(res.message);
        }
        return res
    })
}
