# -*- coding: utf-8 -*-
import icp,json,flask

HOST='127.0.0.1'
PORT=9088

app = flask.Flask(__name__)
json_header = {'Content-Type':'application/json; charset=utf-8'}

@app.route('/',methods=['GET'])
def home():
    domain = flask.request.args.get('domain')
    result = icp.icpquery(domain)
    return flask.Response(json.dumps(result),headers=json_header)

@app.errorhandler(404)
def notfound(e):
    errorStr = '''<html>
<head><title>404 Not Found</title></head>
<body>
<center><h1>404 Not Found</h1></center>
<hr><center>server</center>
</body>
</html>'''
    headers = {
        "Content-Type":"text/html"
    }
    return flask.Response(errorStr,status=404,headers=headers)

if __name__ == '__main__':
    from gevent.pywsgi import WSGIServer
    http_server = WSGIServer((HOST, PORT), app)
    http_server.serve_forever()
    #app.run(port=PORT,host=HOST)
