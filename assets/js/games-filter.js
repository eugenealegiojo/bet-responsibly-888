(() => {
    "use strict";

    let allGames = document.querySelectorAll(`.game`);
    let hideAllGames = () => {
        allGames.forEach(node => {
            node.classList.add('hidegame');
        });
    };
    let showAllGames = () => {
        allGames.forEach(node => {
            node.classList.remove('hidegame');
        });
    };

    document.querySelector(".game-search #game-name").addEventListener('input', e => {
        hideAllGames();

        if (e.target.value.length >= 3) {
            allGames.forEach(node => {
                if (node.dataset.gameName.match(new RegExp(`.*${e.target.value}.*`, 'i'))) {
                    node.classList.remove('hidegame');
                }
            });
        }

        if (e.target.value.length === 0) {
            showAllGames();
        }
    });

    if (document.querySelector(".game-search #game-lines") !== null) {
        document.querySelector(".game-search #game-lines").addEventListener('change', e => {
            if (e.target.value === "0") {
                showAllGames();
            } else {
                hideAllGames();
                allGames.forEach(node => {
                    if (node.dataset.gameLines === e.target.value) {
                        node.classList.remove('hidegame');
                    }
                });
            }
        });
    }

    if (document.querySelector(".game-search #game-provider") !== null) {
        document.querySelector(".game-search #game-provider").addEventListener('change', e => {
            if (e.target.value === "0") {
                showAllGames();
            } else {
                hideAllGames();
                allGames.forEach(node => {
                    if (node.dataset.gameProvider === e.target.value) {
                        node.classList.remove('hidegame');
                    }
                });
            }
        });
    }

    if (document.querySelector(".game-search #game-category") !== null) {
        document.querySelector(".game-search #game-category").addEventListener('change', e => {
            if (e.target.value === "0") {
                showAllGames();
            } else {
                hideAllGames();
                allGames.forEach(node => {
                    if (node.dataset.gameCategory === e.target.value) {
                        node.classList.remove('hidegame');
                    }
                });
            }
        });
    }
})();