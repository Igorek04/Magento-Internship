define([
    'react',
    'react-dom'
], function (React, ReactDOM) {

    function MemeDisplay({ memes }) {
        // if order without memes data
        if (!memes) {
            return React.createElement('span', null, 'Not Found');
        }

        // if order memes data have empty selected field
        if (!memes.selected) {
            return React.createElement('span', null, 'Not Selected');
        }

        // if order memes data has selected url
        const [isOpen, setIsOpen] = React.useState(false); // default modal state (disabled)
        return React.createElement(React.Fragment, null,
            // mini-image in cell
            React.createElement('img', {
                src: memes.selected,
                alt: 'Selected Meme',
                style: { maxHeight: '40px', display: 'block', cursor: 'pointer' },
                onClick: () => setIsOpen(true)
            }),
            // open modal with full sized image on mini-image click
            isOpen && React.createElement('div', {
                style: {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center',
                    zIndex: 9999,
                    cursor: 'pointer'
                },
                onClick: () => setIsOpen(false)
            }, React.createElement('img', {
                src: memes.selected,
                alt: 'Full Meme'
            }))
        );
    }

    return function (config, element) {
        const memesData = config.memes;
        const uid = element.id;
        ReactDOM.render(
            React.createElement(MemeDisplay, { memes: memesData }),
            document.getElementById(uid)
        );
    };
});
