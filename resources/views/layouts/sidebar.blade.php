<nav class="sidebar sidebar-offcanvas" id="sidebar">
      <ul class="nav">
            <li class="nav-item nav-profile">
                  <a href="{{route('dashboard')}}" class="nav-link">
                        <div class="nav-profile-image">
                              <img src="{{asset('assets/images/faces/pic-1.png')}}" alt="profile">
                              <span class="login-status online"></span>
                        </div>
                        <div class="nav-profile-text d-flex flex-column">
                              <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
                        </div>
                        <i class="bi bi-bookmark-check-fill text-success nav-profile-badge"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{ route('dashboard') }}">
                        <span class="menu-title">Dashboard</span>
                        <i class="bi bi-house-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{ route('service.index') }}">
                        <span class="menu-title">Services</span>
                        <i class="bi bi-grid-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{ route('customer')}}">
                        <span class="menu-title">Customers</span>
                        <i class="bi bi-person-check-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{ route('provider') }}">
                        <span class="menu-title">Providers</span>
                        <i class="bi bi-people-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{ route('message') }}">
                        <span class="menu-title">Message/Report</span>
                        <i class="bi bi-envelope-fill menu-icon"></i>
                  </a>
            </li>
            <!-- <li class="nav-item">
                  <a class="nav-link" href="">
                        <span class="menu-title">Banner</span>
                        <i class="bi bi-images menu-icon"></i>
                  </a>
            </li> -->
            <li class="nav-item">
                  <a class="nav-link" href="{{route('faq.index')}}">
                        <span class="menu-title">FAQ's</span>
                        <i class="bi bi-info-square-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{route('page.create','about-us')}}">
                        <span class="menu-title">About us</span>
                        <i class="bi bi-pencil-square menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{route('page.create','privacy-policy')}}">
                        <span class="menu-title">Privacy Policy</span>
                        <i class="bi bi-shield-lock menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{route('page.create','term-of-condition')}}">
                        <span class="menu-title">Term & Condition</span>
                        <i class="bi bi-key-fill menu-icon"></i>
                  </a>
            </li>
            <li class="nav-item">
                  <a class="nav-link" href="{{route('profile.edit')}}">
                        <span class="menu-title">Settings</span>
                        <i class="bi bi-gear-fill menu-icon"></i>
                  </a>
            </li>
            <!-- <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                        <span class="menu-title">Basic UI Elements</span>
                              <i class="menu-arrow"></i>
                              <i class="mdi mdi-crosshairs-gps menu-icon"></i>
                  </a>
                  <div class="collapse" id="ui-basic">
                        <ul class="nav flex-column sub-menu">
                              <li class="nav-item"> <a class="nav-link" href="pages/ui-features/buttons.html">Buttons</a></li>
                              <li class="nav-item"> <a class="nav-link" href="pages/ui-features/typography.html">Typography</a></li>
                        </ul>
                  </div>
            </li> -->
      </ul>
</nav>