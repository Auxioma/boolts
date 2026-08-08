PHP ?= php
CONSOLE := bin/console

.DEFAULT_GOAL := reset

.PHONY: reset
reset:
	@echo "[1/8] Drop database..."
	@$(PHP) $(CONSOLE) doctrine:schema:drop --full-database --force || { status=$$?; echo "ERREUR etape 1"; exit $$status; }
	@echo "[2/8] Update schema..."
	@$(PHP) $(CONSOLE) d:s:u --force || { status=$$?; echo "ERREUR etape 2"; exit $$status; }
	@echo "[3/8] Load fixtures..."
	@$(PHP) $(CONSOLE) doctrine:fixtures:load --no-interaction || { status=$$?; echo "ERREUR etape 3"; exit $$status; }
	@echo "[4/8] Clear cache..."
	@$(PHP) $(CONSOLE) cache:clear --no-interaction || { status=$$?; echo "ERREUR etape 4"; exit $$status; }
	@echo "[5/8] Warmup cache..."
	@$(PHP) $(CONSOLE) cache:warmup --no-interaction || { status=$$?; echo "ERREUR etape 5"; exit $$status; }
	@echo "[6/8] Install assets..."
	@$(PHP) $(CONSOLE) assets:install public --no-interaction || { status=$$?; echo "ERREUR etape 6"; exit $$status; }
	@echo "[7/8] Install importmap..."
	@$(PHP) $(CONSOLE) importmap:install --no-interaction || { status=$$?; echo "ERREUR etape 7"; exit $$status; }
	@echo "[8/8] Compile asset-map..."
	@$(PHP) $(CONSOLE) asset-map:compile || { status=$$?; echo "ERREUR etape 8"; exit $$status; }
	@printf "\nDone!\n"
