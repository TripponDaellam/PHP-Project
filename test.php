<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white">

  <!-- Navigation -->
  <nav class="bg-white dark:bg-gray-950 shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <a href="#" class="text-2xl font-bold text-indigo-600">Method Flow</a>
      <div class="space-x-4 hidden sm:block">
        <a href="#how" class="hover:underline">How It Works</a>
        <a href="#values" class="hover:underline">Community</a>
        <a href="#team" class="hover:underline">Team</a>
        <a href="#faq" class="hover:underline">FAQ</a>
        <a href="#contact" class="hover:underline">Contact</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="bg-gradient-to-br from-indigo-100 to-white dark:from-gray-900 dark:to-gray-950 py-24 text-center px-6">
    <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Welcome to Method Flow</h1>
    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
      A knowledge-sharing community for developers. Ask, answer, and grow your skills in a supportive space.
    </p>
  </section>

  <!-- How It Works -->
  <section id="how" class="py-20 px-6 bg-white dark:bg-gray-900">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">How It Works</h2>
      <p class="text-gray-600 dark:text-gray-400">Simple steps to contribute and learn</p>
    </div>
    <div class="max-w-6xl mx-auto grid gap-8 sm:grid-cols-3">
      <div class="p-6 bg-indigo-50 dark:bg-gray-800 rounded-xl shadow">
        <i data-lucide="help-circle" class="w-8 h-8 mb-2 text-indigo-600"></i>
        <h3 class="font-semibold text-xl">1. Ask Questions</h3>
        <p>Post your issues clearly. Use tags for visibility.</p>
      </div>
      <div class="p-6 bg-indigo-50 dark:bg-gray-800 rounded-xl shadow">
        <i data-lucide="messages-square" class="w-8 h-8 mb-2 text-green-600"></i>
        <h3 class="font-semibold text-xl">2. Get & Share Answers</h3>
        <p>Receive solutions or help others with their questions.</p>
      </div>
      <div class="p-6 bg-indigo-50 dark:bg-gray-800 rounded-xl shadow">
        <i data-lucide="star" class="w-8 h-8 mb-2 text-yellow-500"></i>
        <h3 class="font-semibold text-xl">3. Build Reputation</h3>
        <p>Earn points, badges, and respect by helping others.</p>
      </div>
    </div>
  </section>

  <!-- Community Values -->
  <section id="values" class="py-20 bg-gray-100 dark:bg-gray-800 px-6">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">Community Values</h2>
      <p class="text-gray-600 dark:text-gray-300">The heart of Method Flow</p>
    </div>
    <div class="max-w-5xl mx-auto grid gap-8 sm:grid-cols-2">
      <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow">
        <h3 class="font-semibold text-xl text-indigo-500">Be Respectful</h3>
        <p>Everyone deserves kindness—beginners and experts alike.</p>
      </div>
      <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow">
        <h3 class="font-semibold text-xl text-green-500">Be Helpful</h3>
        <p>Encourage growth through thoughtful feedback and guidance.</p>
      </div>
      <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow">
        <h3 class="font-semibold text-xl text-pink-500">Be Clear</h3>
        <p>Make it easy for others to understand and help.</p>
      </div>
      <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow">
        <h3 class="font-semibold text-xl text-yellow-500">Be Curious</h3>
        <p>Ask questions freely—learning starts with curiosity.</p>
      </div>
    </div>
  </section>

  <!-- Meet the Team -->
  <section id="team" class="py-20 px-6 bg-white dark:bg-gray-900">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">Meet Our Team</h2>
      <p class="text-gray-600 dark:text-gray-300">Creators behind Method Flow</p>
    </div>
    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3 max-w-6xl mx-auto text-center">
      <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-xl shadow">
        <img src="https://via.placeholder.com/100" class="w-24 h-24 mx-auto rounded-full mb-4" alt="Jane">
        <h3 class="font-semibold text-lg">Jane Doe</h3>
        <p class="text-indigo-600 dark:text-indigo-400">Frontend Lead</p>
      </div>
      <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-xl shadow">
        <img src="https://via.placeholder.com/100" class="w-24 h-24 mx-auto rounded-full mb-4" alt="John">
        <h3 class="font-semibold text-lg">John Smith</h3>
        <p class="text-green-600 dark:text-green-400">Backend Developer</p>
      </div>
      <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-xl shadow">
        <img src="https://via.placeholder.com/100" class="w-24 h-24 mx-auto rounded-full mb-4" alt="Lisa">
        <h3 class="font-semibold text-lg">Lisa Ray</h3>
        <p class="text-pink-600 dark:text-pink-400">Community Manager</p>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="py-20 bg-gray-100 dark:bg-gray-800 px-6">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">FAQs</h2>
    </div>
    <div class="max-w-3xl mx-auto space-y-6">
      <div>
        <h3 class="font-semibold text-lg">Is it free to use Method Flow?</h3>
        <p class="text-gray-600 dark:text-gray-300">Yes! Anyone can ask, answer, and learn for free.</p>
      </div>
      <div>
        <h3 class="font-semibold text-lg">Can I answer my own question?</h3>
        <p class="text-gray-600 dark:text-gray-300">Yes, and it’s encouraged if you’ve solved your issue.</p>
      </div>
      <div>
        <h3 class="font-semibold text-lg">How do I earn reputation?</h3>
        <p class="text-gray-600 dark:text-gray-300">By upvotes, accepted answers, and helpful contributions.</p>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact" class="py-20 px-6 bg-white dark:bg-gray-900 text-center">
    <h2 class="text-3xl font-bold mb-4">Get in Touch</h2>
    <p class="text-gray-600 dark:text-gray-300 mb-6">Need help or want to say hello? Reach out!</p>
    <form class="max-w-xl mx-auto space-y-4">
      <input type="text" placeholder="Name" class="w-full p-3 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white" />
      <input type="email" placeholder="Email" class="w-full p-3 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white" />
      <textarea rows="4" placeholder="Message" class="w-full p-3 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded">
        Send Message
      </button>
    </form>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-100 dark:bg-gray-800 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
    © 2025 Method Flow. Built for coders, by coders.
  </footer>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>