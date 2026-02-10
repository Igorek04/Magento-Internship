var config = {
    paths: {
        react: 'https://unpkg.com/react@17/umd/react.production.min',
        'react-dom': 'https://unpkg.com/react-dom@17/umd/react-dom.production.min',
        'react-image-picker': 'https://unpkg.com/react-image-picker/dist/index'
    },
    shim: {
        react: { exports: 'React' },
        'react-dom': { deps: ['react'], exports: 'ReactDOM' },
        'react-image-picker': { deps: ['react', 'react-dom'], exports: 'ImagePicker' }
    }
};
