<!-- Chatbot Flow System Test -->
<!-- Save this in your browser console and test the endpoints -->

// Test 1: Flow Action
fetch('/chatbot/process', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: 'test_session',
        action: 'flow',
        language: 'ar'
    })
})
.then(r => r.json())
.then(data => console.log('Flow Response:', data))
.catch(e => console.error('Flow Error:', e));

// Test 2: Navigate Action
fetch('/chatbot/process', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: 'test_session',
        action: 'navigate',
        language: 'ar',
        flow_key: 'troubleshooting'
    })
})
.then(r => r.json())
.then(data => console.log('Navigate Response:', data))
.catch(e => console.error('Navigate Error:', e));

// Test 3: Navigate with Branch
fetch('/chatbot/process', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: 'test_session',
        action: 'navigate',
        language: 'ar',
        flow_key: 'troubleshooting',
        branch_key: 'billing_issues'
    })
})
.then(r => r.json())
.then(data => console.log('Branch Response:', data))
.catch(e => console.error('Branch Error:', e));
