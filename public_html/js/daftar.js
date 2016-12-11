$.validate({
        modules : 'security', 
          onModulesLoaded : function() {
            var optionalConfig = {
              fontSize: '12pt',
              padding: '4px',
              bad : 'Sangat lemah',
              weak : 'Lemah',
              good : 'Baik',
              strong : 'Sangat kuat'
            };

            $('input[name="password_confirmation"]').displayPasswordStrength(optionalConfig);
          }
      });