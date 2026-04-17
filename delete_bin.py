import requests
url = 'https://api.jsonbin.io/v3/b/<69e1e75a856a682189431276>'
headers = {
  'X-Master-Key': '<69e1e5cb10716a4d5de5c45b>'
}

req = requests.delete(url, json=None, headers=headers)
print(req.text)
