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
                    $('#population.adhesion-simulator-form__item').removeClass('adhesion-simulator-form__item--hidden');
                    $("#edit-population").attr("required", true);
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
            $('#edit-population', context).on( 'keyup', function(event) {
                var total = Math.round(drupalSettings.openig_adhesion.openigAdhesion.formula_population * event.target.value);
                var display = total <=20000 ? total : 20000;
                $('#simulator_type_1').html(display + '€');
                $('#simulation_result').val(display);
            });

            // Salaries formula
            $('#edit-salaries', context).on( 'keyup', function(event) {
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
            });

            // Budget formula
            $('#edit-budget', context).on( 'keyup', function(event) {
                var total = Math.round(drupalSettings.openig_adhesion.openigAdhesion.formula_budget * event.target.value);
                var display = total <= 25000 ? total : 25000;
                $('#simulator_type_3').html(display + '€');
                $('#simulation_result').val(display);
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
                } else {
                    event.target.submit();
                }
            });

        }
    };
}(jQuery, Drupal, drupalSettings));
