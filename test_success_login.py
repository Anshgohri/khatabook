import requests
from bs4 import BeautifulSoup
import json

session = requests.Session()
response = session.get('http://localhost:8000/login')
soup = BeautifulSoup(response.text, 'html.parser')
token = soup.find('input', {'name': '_token'})['value']

post_data = {
    '_token': token,
    'email': 'anshgoel8850@gmail.com',  
    'password': 'password' 
}
# First try JSON request
headers = {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Referer': 'http://localhost:8000/login'}
response_post = session.post('http://localhost:8000/login', data=post_data, headers=headers, allow_redirects=False)

print("POST /login Status:", response_post.status_code)
print("POST /login Content:", response_post.text)
