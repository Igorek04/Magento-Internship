var config = {
    paths: {
        react: 'Perspective_Memes/js/react/react.production.min', // https://unpkg.com/react@17/umd/react.production.min.js
        'react-dom': 'Perspective_Memes/js/react/react-dom.production.min' // https://unpkg.com/react-dom@17/umd/react-dom.production.min.js
    },
    shim: {
        react: { exports: 'React' },
        'react-dom': { deps: ['react'], exports: 'ReactDOM' }
    }
};
