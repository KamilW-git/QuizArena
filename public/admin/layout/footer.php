    </div>
    <script>
        async function adminAction(action, targetType, targetId, extraParams = {}) {
            if (!confirm(`Are you sure you want to perform action: ${action} on ${targetType}?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('target_type', targetType);
                formData.append('target_id', targetId);
                formData.append('csrf_token', '<?= htmlspecialchars(\QuizArena\Helpers\Auth::generateCsrfToken()) ?>');
                
                for (const key in extraParams) {
                    formData.append(key, extraParams[key]);
                }

                const response = await fetch('/api/admin/action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                alert('Network error');
            }
        }
    </script>
</body>
</html>
