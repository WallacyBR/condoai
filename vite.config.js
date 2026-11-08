import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            strategies: 'generateSW',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,jpg,jpeg,gif,woff,woff2,ttf,eot}'],
                maximumFileSizeToCacheInBytes: 5 * 1024 * 1024, // 5MB
                cleanupOutdatedCaches: true,
                skipWaiting: true,
                clientsClaim: true,
                navigateFallback: '/offline',
                navigateFallbackAllowlist: [/^(?!\/__).*/],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'gstatic-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/.*\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24 // 24 horas
                            },
                            networkTimeoutSeconds: 10
                        }
                    },
                    {
                        urlPattern: /^https:\/\/.*\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 * 7 // 7 dias
                            },
                            networkTimeoutSeconds: 5
                        }
                    }
                ]
            },
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
            manifest: {
                name: 'CondoAI - Gestão de Condomínios',
                short_name: 'CondoAI',
                description: 'Sistema inteligente para gestão de condomínios',
                theme_color: '#1e293b',
                background_color: '#0f172a',
                display: 'standalone',
                display_override: ["window-controls-overlay"],
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                lang: 'pt-BR',
                screenshots: [
                    {
                        "src": "/images/condo_ai_horizontal.png",
                        "type": "image/png",
                        "sizes": "1280x720",
                        "form_factor": "wide",
                        "label": "Tela principal do CondoAI"
                    }
                ],
                icons: [
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ]
            }
        })
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    assetsInclude: ['**/*.png', '**/*.jpg', '**/*.jpeg', '**/*.gif', '**/*.svg'],
});
