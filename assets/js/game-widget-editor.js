(function($) {
    'use strict';

    $( () => {        
        elementor.on('document:loaded', function() {
            elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
                const categoryControl = panel.$el.find('.elementor-control-category select');
                const gameTypeControl = panel.$el.find('.elementor-control-game_type select');
                const tagsControl = panel.$el.find('.elementor-control-tags select');
                const sortByControl = panel.$el.find('.elementor-control-sort_by select');
                const gamesOrderControl = panel.$el.find('.elementor-control-games_order');
                const gamesOrderControlInput = gamesOrderControl.find('input');
                const hasLimitControl = panel.$el.find('.elementor-control-limit input');
                const gameCountControlWrap = panel.$el.find('.elementor-control-count');
                const gameCountControl = gameCountControlWrap.find('input');
                
                let tagifyInstance;

                async function initializeGamesOrder() {
                    if (sortByControl.val() !== 'custom') {
                        return;
                    }

                    const gamesList = await fetchGamesList();
                    const gameTypeValues = gameTypeControl.val() || [];
                    const tagsValues = tagsControl.val() || [];
                    let savedOrder = model.get('settings').get('games_order') || '';

                    // Ensure savedOrder is a string
                    if (Array.isArray(savedOrder)) {
                        savedOrder = savedOrder.join(',');
                    } else if (typeof savedOrder !== 'string') {
                        savedOrder = String(savedOrder);
                    }
                    const gamesArray = savedOrder.split(',').map(value => ({ value }));

                    let filteredGamesList = applyFilters(gamesList, gameTypeValues, tagsValues);
                    if (!gameCountControlWrap.hasClass('elementor-hidden-control')) {
                        filteredGamesList = filteredGamesList.slice(0, parseInt(gameCountControl.val()));
                    }

                    if (tagifyInstance) {
                        // tagifyInstance.destroy();

                        // Update the existing Tagify instance with new tags
                        tagifyInstance.loadOriginalValues();
                        tagifyInstance.addTags(gamesArray);
                    } else {
                    
                        tagifyInstance = new Tagify(gamesOrderControlInput[0], {
                            whitelist: filteredGamesList || [],
                            enforceWhitelist: true,
                            backspace: false,
                            // maxTags: 10,
                            dropdown: {
                                appendTarget: gamesOrderControl.find('.elementor-control-input-wrapper')[0],
                                searchKeys: ['value', 'name'],
                                maxItems: 20,
                                classname: 'games-list',
                                enabled: 0,
                                closeOnSelect: false,
                                position: 'all',
                            },
                            delimiters: ',| ',
                            originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),
                            templates: {
                                tag: function(tagData) {
                                    let displayName = tagData.name;

                                    if (gamesList.length > 0 && tagData.name === undefined) {
                                        const tagItem = gamesList.find(item => item.value === tagData.value);
                                        if (tagItem && typeof tagItem.name !== 'undefined') {
                                            displayName = tagItem.name;
                                        }
                                    }

                                    if (displayName === undefined) {
                                        displayName = tagData.value;
                                    }

                                    return `<tag title="${tagData.value}" contenteditable="false" spellcheck="false" tabIndex="-1" class="tagify__tag ${tagData.class ? tagData.class : ''}" ${this.getAttributes(tagData)}>
                                                <x title="remove tag" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x>
                                                <div>
                                                    <span class="tagify__tag-text">${displayName}</span>
                                                </div>
                                            </tag>`;
                                },
                                dropdownItem: function(tagData) {
                                    return `<div ${this.getAttributes(tagData)} class="tagify__dropdown__item ${tagData.class ? tagData.class : ''}">
                                                <strong>${tagData.name}</strong>
                                            </div>`;
                                },
                            },
                        });

                        tagifyInstance.addTags(gamesArray);
                    }

                    tagifyInstance.on( 'change', () => {
                        const newValue = tagifyInstance.value.map(tag => tag.value).join(',');

                        // Update widget model
                        if (model) {
                           
                            model.setSetting('games_order', newValue);
                            // model.trigger('changed:games_order');
                            // model.set('settings', model.get('settings'));
                            model.trigger('change:settings');


                            // Mark as changed to enable the Update button
                            // elementor.channels.editor.trigger('element:edited', {
                            //     // container: panel,
                            //     view: view,
                            //     model: model
                            // });

                            gamesOrderControlInput.trigger('change');

                            elementor.channels.editor.trigger('status:change'); // change:editSettings
                            elementor.saver.trigger('save', { silent: true });

                            // Trigger remote render to refresh the preview
                            model.renderRemoteServer();
                        }
                    });
                }

                categoryControl.on('change', initializeGamesOrder);
                gameTypeControl.on('change', initializeGamesOrder);
                tagsControl.on('change', initializeGamesOrder);
                sortByControl.on('change', initializeGamesOrder);
                gameCountControl.on('change', initializeGamesOrder);
                hasLimitControl.on('change', initializeGamesOrder);

                // Initial call to set up Tagify
                initializeGamesOrder();
            });

            async function fetchGamesList() {
                if (ParlayGamesList.game_data_json_url === '') {
                    console.log('game_data_json_url is not set');
                    return [];
                }
    
                try {
                    const response = await fetch(ParlayGamesList.game_data_json_url);
                    if (!response.ok) {
                        throw new Error('Failed to fetch suggestions');
                    }
                    const data = await response.json();
                    return data.map(item => ({
                        value: item.gameId,
                        name: item.name,
                        type: item.type,
                        tags: item.tags
                    }));
                } catch (error) {
                    console.error('Error fetching JSON:', error);
                    return [];
                }
            }
    
            function applyFilters(gamesList, gameType, tags) {
                const filteredGames = gamesList.filter(game => {
                    let matchesType = true;
                    let matchesTags = true;
    
                    if (gameType.length > 0) {
                        matchesType = gameType.includes(game.type);
                    }
    
                    if (tags.length > 0) {
                        matchesTags = game.tags && tags.every(tag => game.tags.includes(tag));
                    }
    
                    return matchesType && matchesTags;
                });
    
                return filteredGames;
            }
        });   
    });

})(jQuery);
