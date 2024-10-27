import React from 'react'
import '../assets/styles/footer.css';


const FooterComponent = ( {name, group} ) => {
    const currentYear = new Date().getFullYear();

    return (
        <footer className='footer'>
            <p className='footer-text'>
                {currentYear}, Grupo: {group} - {name}.
            </p>
            <a
                href="https://github.com/gregfc95/php-gameReview"
                target="_blank"
                rel="noopener noreferrer"
                className='footer-link'
            >
                GitHub
            </a>
        </footer>
    );
};

export default FooterComponent