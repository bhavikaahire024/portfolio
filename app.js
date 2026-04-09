const storageKeys = {
  users: 'prepwise_users',
  currentUser: 'prepwise_current_user'
};

const demoFeedback = [
  'Strong structure in your STAR responses.',
  'Work on reducing filler words for better clarity.',
  'Great domain knowledge in system design answers.'
];

const demoSuggestions = [
  'Senior Frontend Engineer (React + Architecture)',
  'Behavioral Leadership Round (Managerial focus)',
  'API & Integration Problem Solving Interview'
];

const ui = {
  signinTab: document.getElementById('signinTab'),
  signupTab: document.getElementById('signupTab'),
  signinForm: document.getElementById('signinForm'),
  signupForm: document.getElementById('signupForm'),
  authMessage: document.getElementById('authMessage'),
  authSection: document.getElementById('authSection'),
  dashboardSection: document.getElementById('dashboardSection'),
  logoutBtn: document.getElementById('logoutBtn'),
  welcomeTitle: document.getElementById('welcomeTitle'),
  feedbackList: document.getElementById('feedbackList'),
  suggestionList: document.getElementById('suggestionList'),
  overallScore: document.getElementById('overallScore'),
  meterBar: document.getElementById('meterBar'),
  ratingHint: document.getElementById('ratingHint')
};

function getUsers() {
  return JSON.parse(localStorage.getItem(storageKeys.users) || '[]');
}

function setUsers(users) {
  localStorage.setItem(storageKeys.users, JSON.stringify(users));
}

function setCurrentUser(user) {
  localStorage.setItem(storageKeys.currentUser, JSON.stringify(user));
}

function getCurrentUser() {
  return JSON.parse(localStorage.getItem(storageKeys.currentUser) || 'null');
}

function showMessage(text, type = '') {
  ui.authMessage.textContent = text;
  ui.authMessage.className = `message ${type}`.trim();
}

function switchTab(mode) {
  const isSignIn = mode === 'signin';
  ui.signinTab.classList.toggle('active', isSignIn);
  ui.signupTab.classList.toggle('active', !isSignIn);
  ui.signinTab.setAttribute('aria-selected', String(isSignIn));
  ui.signupTab.setAttribute('aria-selected', String(!isSignIn));
  ui.signinForm.classList.toggle('active', isSignIn);
  ui.signupForm.classList.toggle('active', !isSignIn);
  showMessage('');
}

function buildRating(user) {
  const base = user?.name?.length || 5;
  const score = Math.min(10, Math.max(6, (base % 5) + 6));
  return score;
}

function renderDashboard(user) {
  ui.authSection.classList.add('hidden');
  ui.dashboardSection.classList.remove('hidden');
  ui.logoutBtn.classList.remove('hidden');
  ui.welcomeTitle.textContent = `Welcome, ${user.name}`;

  ui.feedbackList.innerHTML = demoFeedback.map(item => `<li>${item}</li>`).join('');
  ui.suggestionList.innerHTML = demoSuggestions.map(item => `<li>${item}</li>`).join('');

  const score = buildRating(user);
  ui.overallScore.textContent = score;
  ui.meterBar.style.width = `${score * 10}%`;
  ui.ratingHint.textContent =
    score >= 8
      ? 'Excellent momentum. Focus on advanced scenarios next.'
      : 'Good start. Practice consistency for a higher rating.';
}

function showAuthView() {
  ui.authSection.classList.remove('hidden');
  ui.dashboardSection.classList.add('hidden');
  ui.logoutBtn.classList.add('hidden');
}

ui.signinTab.addEventListener('click', () => switchTab('signin'));
ui.signupTab.addEventListener('click', () => switchTab('signup'));

ui.signupForm.addEventListener('submit', event => {
  event.preventDefault();
  const data = new FormData(ui.signupForm);
  const name = String(data.get('name') || '').trim();
  const email = String(data.get('email') || '').toLowerCase().trim();
  const password = String(data.get('password') || '');

  if (!name || !email || password.length < 6) {
    showMessage('Please provide valid signup details.', 'error');
    return;
  }

  const users = getUsers();
  if (users.some(user => user.email === email)) {
    showMessage('Account already exists. Please sign in.', 'error');
    return;
  }

  const newUser = { name, email, password };
  users.push(newUser);
  setUsers(users);
  setCurrentUser(newUser);
  showMessage('Account created successfully.', 'success');
  renderDashboard(newUser);
  ui.signupForm.reset();
});

ui.signinForm.addEventListener('submit', event => {
  event.preventDefault();
  const data = new FormData(ui.signinForm);
  const email = String(data.get('email') || '').toLowerCase().trim();
  const password = String(data.get('password') || '');

  const user = getUsers().find(item => item.email === email && item.password === password);
  if (!user) {
    showMessage('Invalid email or password.', 'error');
    return;
  }

  setCurrentUser(user);
  showMessage('Signed in successfully.', 'success');
  renderDashboard(user);
  ui.signinForm.reset();
});

ui.logoutBtn.addEventListener('click', () => {
  localStorage.removeItem(storageKeys.currentUser);
  showAuthView();
  switchTab('signin');
  showMessage('You have been logged out.', 'success');
});

(function init() {
  const currentUser = getCurrentUser();
  if (currentUser) {
    renderDashboard(currentUser);
  } else {
    switchTab('signin');
    showAuthView();
  }
})();
