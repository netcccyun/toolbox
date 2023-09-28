# -*- coding: utf-8 -*-
import requests,hashlib,time,base64,cv2,os
def icpquery(info):
    if(info == None or len(info) == 0):
        return {'code':-1,'msg':'no domain'}
    info_data = {
        'pageNum':'',
        'pageSize':'',
        'unitName':info,
        'serviceType':1
    }

    #构造AuthKey
    timeStamp = int(round(time.time()*1000))
    authSecret = 'testtest' + str(timeStamp)
    authKey = hashlib.md5(authSecret.encode(encoding='UTF-8')).hexdigest()
    #获取Cookie
    cookie_headers = {
    'accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
    'accept-encoding': 'gzip, deflate, br',
    'accept-language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
    'user-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36 Edg/90.0.818.42'
    }
    cookie = requests.utils.dict_from_cookiejar(requests.get('https://beian.miit.gov.cn/',headers=cookie_headers,verify=False).cookies)['__jsluid_s']
    #请求获取Token
    t_url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/auth'
    t_headers = {
        'Host': 'hlwicpfwc.miit.gov.cn',
        'Connection': 'keep-alive',
        'sec-ch-ua': '" Not A;Brand";v="99", "Chromium";v="90", "Microsoft Edge";v="90"',
        'Accept': '*/*',
        'DNT': '1',
        'sec-ch-ua-mobile': '?0',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46',
        'Origin': 'https://beian.miit.gov.cn',
        'Sec-Fetch-Site': 'same-site',
        'Sec-Fetch-Mode': 'cors',
        'Sec-Fetch-Dest': 'empty',
        'Referer': 'https://beian.miit.gov.cn/',
        'Accept-Encoding': 'gzip, deflate, br',
        'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        'Cookie': '__jsluid_s=' + cookie
    }
    data = {
        'authKey': authKey,
        'timeStamp': timeStamp
    }
    t_response = requests.post(t_url,data=data,headers=t_headers,verify=False)
    try:
        get_token = t_response.json()['params']['bussiness']
    except:
        return {'code':-1,'msg':'请求被禁止，请稍后或更换头部与IP后再试('+t_response.status_code+')'}

    #获取验证图像、UUID
    p_url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/image/getCheckImage'
    p_headers = {
        'Host': 'hlwicpfwc.miit.gov.cn',
        'Connection': 'keep-alive',
        'Content-Length': '0',
        'sec-ch-ua': '" Not A;Brand";v="99", "Chromium";v="90", "Microsoft Edge";v="90"',
        'Accept': 'application/json, text/plain, */*',
        'DNT': '1',
        'sec-ch-ua-mobile': '?0',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46',
        'token': get_token,
        'Origin': 'https://beian.miit.gov.cn',
        'Sec-Fetch-Site': 'same-site',
        'Sec-Fetch-Mode': 'cors',
        'Sec-Fetch-Dest': 'empty',
        'Referer': 'https://beian.miit.gov.cn/',
        'Accept-Encoding': 'gzip, deflate, br',
        'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        'Cookie': '__jsluid_s=' + cookie
    }
    p_request = requests.post(p_url,data='',headers=p_headers,verify=False)
    try:
        p_uuid = p_request.json()['params']['uuid']
        big_image = p_request.json()['params']['bigImage']
        small_image = p_request.json()['params']['smallImage']
    except KeyError:
        return {'code':-1,'msg':'获取验证图像失败，请重试('+p_request.status_code+')'}

    #解码图片，写入并计算图片缺口位置
    with open('bigImage.jpg','wb') as f:
        f.write(base64.b64decode(big_image))
        f.close()
    with open('smallImage.jpg','wb') as f:
        f.write(base64.b64decode(small_image))
        f.close()
    background_image = cv2.imread('bigImage.jpg',cv2.COLOR_GRAY2RGB)
    fill_image = cv2.imread('smallImage.jpg',cv2.COLOR_GRAY2RGB)
    background_image_canny = cv2.Canny(background_image, 100, 200)
    fill_image_canny = cv2.Canny(fill_image, 100, 300)
    position_match = cv2.matchTemplate(background_image, fill_image, cv2.TM_CCOEFF_NORMED)
    min_val,max_val,min_loc,max_loc = cv2.minMaxLoc(position_match)
    position = max_loc
    mouse_length = position[0]+1
    os.remove('bigImage.jpg')
    os.remove('smallImage.jpg')

    #通过拼图验证，获取sign
    check_url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/image/checkImage'
    check_headers = {
        'Host': 'hlwicpfwc.miit.gov.cn',
        'Accept': 'application/json, text/plain, */*',
        'Connection': 'keep-alive',
        'Content-Length': '60',
        'sec-ch-ua': '" Not A;Brand";v="99", "Chromium";v="90", "Microsoft Edge";v="90"',
        'DNT': '1',
        'sec-ch-ua-mobile': '?0',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36 Edg/90.0.818.42',
        'token': get_token,
        'Content-Type': 'application/json',
        'Origin': 'https://beian.miit.gov.cn',
        'Sec-Fetch-Site': 'same-site',
        'Sec-Fetch-Mode': 'cors',
        'Sec-Fetch-Dest': 'empty',
        'Referer': 'https://beian.miit.gov.cn/',
        'Accept-Encoding': 'gzip, deflate, br',
        'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        'Cookie': '__jsluid_s=' + cookie
    }
    check_data = {
        'key':p_uuid,
        'value':mouse_length
    }
    check_request = requests.post(check_url,json=check_data,headers=check_headers,verify=False)
    try:
        sign = check_request.json()['params']
    except Exception:
        return {'code':-1,'msg':'校验图片信息失败，请重试('+check_request.status_code+')'}


    #获取备案信息
    info_url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/icpAbbreviateInfo/queryByCondition'
    info_headers = {
        'Host': 'hlwicpfwc.miit.gov.cn',
        'Connection': 'keep-alive',
        'Content-Length': '78',
        'sec-ch-ua': '" Not A;Brand";v="99", "Chromium";v="90", "Microsoft Edge";v="90"',
        'DNT': '1',
        'sec-ch-ua-mobile': '?0',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36 Edg/90.0.818.42',
        'Content-Type': 'application/json',
        'Accept': 'application/json, text/plain, */*',
        'uuid': p_uuid,
        'token': get_token,
        'sign': sign,
        'Origin': 'https://beian.miit.gov.cn',
        'Sec-Fetch-Site': 'same-site',
        'Sec-Fetch-Mode': 'cors',
        'Sec-Fetch-Dest': 'empty',
        'Referer': 'https://beian.miit.gov.cn/',
        'Accept-Encoding': 'gzip, deflate, br',
        'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        'Cookie': '__jsluid_s=' + cookie
    }
    info_request = requests.post(info_url,json=info_data,headers=info_headers,verify=False)
    domain_total = info_request.json()['params']['total']
    result_list = []

    for info_base in info_request.json()['params']['list']:
        result_list.append({'domain':info_base['domain'], 'mainLicence':info_base['mainLicence'], 'webLicence':info_base['serviceLicence'], 'unitName':info_base['unitName'], 'unitType':info_base['natureName'], 'updateTime':info_base['updateRecordTime'], 'limitAccess':info_base['limitAccess'], 'contentTypeName':info_base['contentTypeName']})

    return {'code':0,'msg':'success','data':result_list, 'total':domain_total}
