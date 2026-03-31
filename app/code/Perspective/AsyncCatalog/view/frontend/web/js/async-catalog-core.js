define(['alpine', 'module'], function (Alpine, module) {
    'use strict';

    window.catalogRegistry = window.catalogRegistry || {};

    const Core = {
        register(name, factory) {
            window.catalogRegistry[name] = factory;
        },

        create(config) {
            const proxyRef = { current: null };

            const rootAccessor = new Proxy({}, {
                get(_, prop) {
                    return proxyRef.current ? proxyRef.current[prop] : undefined;
                },
                set(_, prop, value) {
                    if (proxyRef.current) {
                        proxyRef.current[prop] = value;
                    }
                    return true;
                }
            });

            const root = {
                config: config || {},
                init() {
                    proxyRef.current = this;

                    Object.keys(window.catalogRegistry).forEach(name => {
                        if (this[name] && typeof this[name].init === 'function') {
                            this[name].init();
                        }
                    });
                }
            };

            Object.keys(window.catalogRegistry).forEach(name => {
                root[name] = {};
            });

            Object.keys(window.catalogRegistry).forEach(name => {
                const componentData = window.catalogRegistry[name](rootAccessor, config);
                Object.assign(root[name], componentData);
            });

            return root;
        }
    };

    window.catalogCore = Core;

    const reqConfig = module.config() || {};
    const plugins = reqConfig.plugins || [];
    const extensions = reqConfig.extensions || [];

    if (plugins.length) {
        console.log('[Core] Loading Alpine plugins:', plugins);
        require(plugins, function() {
            Array.from(arguments).forEach(plugin => {
                if (plugin && typeof Alpine.plugin === 'function') {
                    Alpine.plugin(plugin);
                }
            });

            loadExtensions();
        });
    } else {
        loadExtensions();
    }

    function loadExtensions() {
        if (extensions.length) {
            console.log('[Core] Loading extensions:', extensions);
        }

        require(extensions, function() {
            if (!window.Alpine.initialized) {
                setTimeout(() => {
                    Alpine.start();
                    window.Alpine.initialized = true;
                });
            }
        });
    }

    return Core;
});
