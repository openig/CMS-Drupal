(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.openigAdhesion = {
        attach: function (context, settings) {
            $('#edit-organism-type', context).on( 'change', function(event) {

                // Hide all
                $('.adhesion-simulator-form__item').addClass('adhesion-simulator-form__item--hidden');

                // Unset required to all
                $("#edit-population").attr("required", false);
                $("#edit-salaries").attr("required", false);
                $("#edit-budget").attr("required", false);

                // Blank fields & formula
                $("#edit-population").val('');
                $("#edit-salaries").val('');
                $("#edit-budget").val('');
                $('#simulation_result').val('');
                $('#simulator_type_1').html('...');
                $('#simulator_type_2').html('...');
                $('#simulator_type_3').html('...');

                // Population
                if (event.target.value > 0 && event.target.value <= 8) {
                    // Récupération de l'organisme selectionné
                    let selectedPopulation = $('#edit-organism-type option:selected').text().trim();
                    // Recherche du montant lié au type d'organisme selectionné
                    let montantFixe = partFixeOrganismeCat1(selectedPopulation);
                    $('#simulator_type_1_part_fixe_organisme').html(montantFixe + '€');
                    $("#edit-population").attr("required", true);
                    $('#population.adhesion-simulator-form__item').removeClass('adhesion-simulator-form__item--hidden');
                }

                // Salaries
                if (event.target.value > 8 && event.target.value <= 12) {
                    $('#salaries.adhesion-simulator-form__item').removeClass('adhesion-simulator-form__item--hidden');
                    $("#edit-salaries").attr("required", true);
                }

                // Budget
                if (event.target.value > 12 && event.target.value <= 13) {
                    $('#budget.adhesion-simulator-form__item').removeClass('adhesion-simulator-form__item--hidden');
                    $("#edit-budget").attr("required", true);
                }

                // Individuel
                if (event.target.value > 13 && event.target.value <= 15) {
                    $('#individual.adhesion-simulator-form__item').removeClass('adhesion-simulator-form__item--hidden');
                }

            });

            // Population formula
            $('#edit-population', context).on( 'input keyup', function(event) {
                // Récupération de l'organisme selectionné
                let selectedPopulation = $('#edit-organism-type option:selected').text().trim();
                // Recherche du montant lié au type d'organisme selectionné
                let montantFixe = partFixeOrganismeCat1(selectedPopulation);

                if (Number.isInteger(parseFloat(event.target.value))) {
                  var total = Math.round(drupalSettings.openig_adhesion.openigAdhesion.formula_population * event.target.value + montantFixe);
                  var display = total <= drupalSettings.openig_adhesion.openigAdhesion.population_part_variable ? total : drupalSettings.openig_adhesion.openigAdhesion.population_part_variable;
                  $('#simulator_type_1').html(display + '€');
                  $('#simulation_result').val(display);
                }
                else { $('#simulator_type_1').html('...€'); }
            });

            // Salaries formula
            $('#edit-salaries', context).on( 'input keyup', function(event) {
                if (Number.isInteger(parseFloat(event.target.value))) {
                  if (event.target.value < 20) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_1 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_1);
                  }
                  if (event.target.value >= 20 && event.target.value <= 50) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_2 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_2);
                  }
                  if (event.target.value >= 51 && event.target.value <= 250) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_3 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_3);
                  }
                  if (event.target.value >= 251 && event.target.value <= 500) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_4 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_4);
                  }
                  if (event.target.value >= 501 && event.target.value <= 1000) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_5 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_5);
                  }
                  if (event.target.value >= 1001 && event.target.value <= 9999) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_6 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_6);
                  }
                  if (event.target.value >= 10000) {
                    $('#simulator_type_2').html(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_7 + '€');
                    $('#simulation_result').val(drupalSettings.openig_adhesion.openigAdhesion.formula_salaries_7);
                  }
                }
                else{ $('#simulator_type_2').html('...€'); }
            });

            // Budget formula
            $('#edit-budget', context).on( 'input keyup', function(event) {
                var total = Math.round(drupalSettings.openig_adhesion.openigAdhesion.formula_budget * event.target.value);
                var display = total <= drupalSettings.openig_adhesion.openigAdhesion.organisme_part_variable ? total : drupalSettings.openig_adhesion.openigAdhesion.organisme_part_variable;
                if (Number.isInteger(parseFloat(event.target.value))) {
                  $('#simulator_type_3').html(display + '€');
                  $('#simulation_result').val(display);
                }
                else { $('#simulator_type_3').html('...€'); }
            });

            function validateNumber(evt) {
                var theEvent = evt || window.event;

                // Handle paste
                if (theEvent.type === 'paste') {
                    key = event.clipboardData.getData('text/plain');
                } else {
                    // Handle key press
                    var key = theEvent.keyCode || theEvent.which;
                    key = String.fromCharCode(key);
                }
                var regex = /[0-9]|\./;
                if( !regex.test(key) ) {
                    theEvent.returnValue = false;
                    if(theEvent.preventDefault) theEvent.preventDefault();
                }
            }

            function validateEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }

            $('#edit-population', context).on( 'keypress', validateNumber);
            $('#edit-salaries', context).on( 'keypress', validateNumber);
            $('#edit-budget', context).on( 'keypress', validateNumber);

            $('#openig-adhesion-simulator-form').on('submit', function (event) {
                event.preventDefault();
                if (!validateEmail($('#edit-email').val())) {
                    $('#edit-email').focus();
                    $('#email_format_error').html('Votre adresse est invalide');
                }
                else if($('#edit-population').val() !== "" && !Number.isInteger(parseFloat($('#edit-population').val()))) {
                  $('#edit-population').focus();
                }
                else if($('#edit-budget').val() !== "" && !Number.isInteger(parseFloat($('#edit-budget').val()))) {
                  $('#edit-budget').focus();
                }
                else if($('#edit-salaries').val() !== "" && !Number.isInteger(parseFloat($('#edit-salaries').val()))) {
                  $('#edit-salaries').focus();
                }
                else {
                    event.target.submit();
                }
            });


          /**
           * Récupére la valeur de la part fixe lié au type d'organisme
           * @param selectedPopulation
           */
          function partFixeOrganismeCat1(selectedPopulation)
            {
              // Options du selecteur + montants associé paramètré
              let options_population = drupalSettings.openig_adhesion.openigAdhesion.options_type_organisme;
              // Recherche du montant lié au type d'organisme selectionné
              let result = options_population.find(item => item.label === selectedPopulation);
              return Number(result.amount) || 0;
            }

        }
    };
}(jQuery, Drupal, drupalSettings));
