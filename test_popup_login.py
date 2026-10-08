import requests
from bs4 import BeautifulSoup

session = requests.Session()
# Visit home page to get CSRF token and simulate being on home page
response = session.get('http://localhost:8000/')
print("GET / Status:", response.status_code)

soup = BeautifulSoup(response.text, 'html.parser')
token = soup.find('input', {'name': '_token'})['value']

# Post with wrong credentials, simulate submitting popup form from home page
post_data = {
    '_token': token,
    'email': 'wrong@test.com',
    'password': 'wrong'
}
headers = {'Referer': 'http://localhost:8000/'}
response_post = session.post('http://localhost:8000/login', data=post_data, headers=headers, allow_redirects=False)

print("POST /login Status:", response_post.status_code)
print("Redirect Location:", response_post.headers.get('Location'))
