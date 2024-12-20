import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/styles/index.css';
import App from './App';
import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from './services/AuthProvider';

function renderApp() {
  const root = ReactDOM.createRoot(document.getElementById('root'));
  root.render(
    <React.StrictMode>
      <BrowserRouter>
        <AuthProvider>
        <App />
        </AuthProvider>
      </BrowserRouter>
    </React.StrictMode>
  );
}

renderApp();