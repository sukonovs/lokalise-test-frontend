## Reqs
1) Docker, Docker compose, Makefile, Git (for git clone)
2) Yarn, Firefox on host machine (if you want to run end 2 end test)

## Installation
1) Clone/Download repo
2) run ``make build`` to build containers
2) run ``make setup`` to setup project
3) run ``make test`` to run tests
4) run ``make end `` to stop and remove task containers
5) (optionally) run ``make test_e2e`` to test frontend

## Notes
1) "Any improvements are welcome and considered a plus." If you have something in mind, then send me email, I will add. Pagination and web sockets (which adds comments from another client) probably could be nice additions which are not included here.
2) I added end to end tests, but have not managed setup them correctly in docker. So if reviewer has Firefox on his machine he can run it with gecko driver.
