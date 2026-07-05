<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /admin/dashboard.php");
    } else {
        header("Location: /staff/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIPJAD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#4338ca',
                        accent: '#c084fc',
                    },
                    animation: {
                        blob: "blob 7s infinite",
                    },
                    keyframes: {
                        blob: {
                            "0%": { transform: "translate(0px, 0px) scale(1)" },
                            "33%": { transform: "translate(30px, -50px) scale(1.1)" },
                            "66%": { transform: "translate(-20px, 20px) scale(0.9)" },
                            "100%": { transform: "translate(0px, 0px) scale(1)" },
                        },
                    },
                }
            }
        }
    </script>
    <style>
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen relative overflow-hidden font-sans px-4 sm:px-6">
    <div class="absolute top-[-10%] left-[-10%] w-72 h-72 sm:w-96 sm:h-96 bg-primary/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
    <div class="absolute top-[20%] right-[-10%] w-72 h-72 sm:w-96 sm:h-96 bg-accent/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-[10%] left-[20%] w-72 h-72 sm:w-96 sm:h-96 bg-pink-300/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>

    <div class="bg-white/80 backdrop-blur-xl p-6 sm:p-10 rounded-3xl shadow-2xl shadow-indigo-500/10 w-full max-w-md relative z-10 border border-white my-6">
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight mb-2">Welcome Back</h1>
            <p class="text-slate-500 text-sm sm:text-base font-medium">Log in to SIPJAD System</p>
        </div>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3" role="alert">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['error_msg']); ?></span>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3" role="alert">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['success_msg']); ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="space-y-4 sm:space-y-5">
            <div>
                <label for="username" class="block text-slate-700 text-sm font-semibold mb-2">Username / Email</label>
                <input type="text" name="username" id="username" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400 text-sm sm:text-base" placeholder="Enter your username" required>
            </div>
            <div>
                <label for="password" class="block text-slate-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400 text-sm sm:text-base" placeholder="••••••••" required>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary text-white font-bold py-3 sm:py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95 text-sm sm:text-base">
                    Sign In
                </button>
            </div>
        </form>
        <p class="text-center text-slate-500 text-xs sm:text-sm mt-6 sm:mt-8 font-medium">
            Belum punya akun? <a href="register.php" class="text-primary hover:text-secondary font-bold hover:underline decoration-2 underline-offset-4 transition-all">Daftar di sini</a>
        </p>
    </div>
</body>
</html>