const http = require('http');
const https = require('https');
const { URL } = require('url');
const readline = require('readline');

const API_URL = 'http://127.0.0.1:8000/api';
const TEST_EMAIL = 'aleksandrtarasovi44@gmail.com';
const TEST_PASSWORD = '5645856adf';

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

const question = (query) => new Promise((resolve) => rl.question(query, resolve));

async function makeRequest(method, endpoint, data, token = null) {
  const url = `${API_URL}${endpoint}`;
  const headers = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const options = {
    method,
    headers,
  };

  return new Promise((resolve, reject) => {
    const parsedUrl = new URL(url);
    const requestModule = parsedUrl.protocol === 'https:' ? https : http;

    const req = requestModule.request(url, options, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => {
        console.log(`Response status: ${res.statusCode}`);
        console.log(`Response headers:`, res.headers);
        console.log(`Response body (first 500 characters):`, data.substring(0, 500));

        let parsedData;
        try {
          parsedData = JSON.parse(data);
        } catch (e) {
          console.log('Response is not valid JSON. It might be HTML or plain text.');
          parsedData = { htmlResponse: data };
        }

        if (res.statusCode >= 400) {
          reject(new Error(`HTTP error! status: ${res.statusCode}`));
        } else {
          resolve(parsedData);
        }
      });
    });

    req.on('error', (error) => {
      console.error(`Request error: ${error.message}`);
      reject(error);
    });

    if (data) {
      req.write(JSON.stringify(data));
    }
    req.end();
  });
}

async function test2FA() {
  try {
    console.log('Testing 2FA functionality...');

    // Step 1: Login
    console.log('Login:');
    const loginResponse = await makeRequest('POST', '/login', { email: TEST_EMAIL, password: TEST_PASSWORD });
    console.log('Pass');
    const token = loginResponse.token;

    // Step 2: Initialize 2FA
    console.log('Initialize 2FA:');
    await makeRequest('POST', '/2fa/init', { email: TEST_EMAIL }, token);
    console.log('Pass');

    // Step 3: Verify 2FA
    const verificationCode = await question('Enter the 2FA code sent to your email: ');
    console.log('Verify 2FA:');
    await makeRequest('POST', '/2fa/verify', { code: verificationCode }, token);
    console.log('Pass');

    // Step 4: Login with 2FA enabled
    console.log('Login with 2FA enabled:');
    const login2FAResponse = await makeRequest('POST', '/login', { email: TEST_EMAIL, password: TEST_PASSWORD });
    console.log('Pass');
    const userId = login2FAResponse.user_id;

    // Step 5: Verify 2FA code for login
    const loginVerificationCode = await question('Enter the 2FA code sent to your email for login: ');
    console.log('Verify 2FA code for login:');
    const verifyLoginResponse = await makeRequest('POST', '/2fa/verify-code', { user_id: userId, code: loginVerificationCode });
    console.log('Pass');
    const newToken = verifyLoginResponse.token;

    // Step 6: Disable 2FA
    console.log('Disable 2FA:');
    await makeRequest('POST', '/2fa/disable', {}, newToken);
    console.log('Pass');

    console.log('2FA testing completed successfully.');
  } catch (error) {
    console.error('Test failed:', error.message);
  } finally {
    rl.close();
  }
}

test2FA();
