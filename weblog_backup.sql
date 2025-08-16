-- MySQL dump 10.13  Distrib 9.4.0, for macos15.4 (arm64)
--
-- Host: localhost    Database: weblog
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Technology','Posts about technology, programming, and software development','2025-08-13 23:42:16'),(2,'Lifestyle','Posts about personal life, habits, and daily experiences','2025-08-13 23:42:16'),(3,'Travel','Posts about travel experiences, destinations, and tips','2025-08-13 23:42:16'),(4,'Food','Posts about cooking, recipes, and food experiences','2025-08-13 23:42:16'),(5,'Education','Posts about learning, tutorials, and educational content','2025-08-13 23:42:16');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_posts_user_id` (`user_id`),
  KEY `idx_posts_created_at` (`created_at`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (2,'First Blog Post','This is my first blog post. Welcome to my blog!',1,'2025-08-13 23:35:14',NULL,NULL),(3,'Second Post','This is my second blog post. I am learning PHP and MySQL!',1,'2025-08-13 23:35:20',NULL,NULL),(4,'Third Post','This is my third blog post. Building a blog system is fun!',1,'2025-08-13 23:35:26',NULL,NULL),(7,'The Art of Photography: Capturing Life Through a Lens','Photography is more than just taking pictures; it is an art form that allows us to capture moments, emotions, and stories that would otherwise be lost in time. Every photograph tells a unique story, and every photographer has their own perspective on how to tell that story.\n\n## The Essence of Photography\n\nAt its core, photography is about seeing the world differently. It is about finding beauty in the ordinary, capturing the extraordinary in the mundane, and preserving memories that will last a lifetime. Whether you are using a professional DSLR camera or a simple smartphone, the principles remain the same: composition, lighting, and timing.\n\n## Understanding Composition\n\nComposition is the foundation of great photography. It is how you arrange the elements within your frame to create a visually appealing and meaningful image. The rule of thirds is one of the most fundamental principles of composition. Imagine your frame divided into nine equal parts by two horizontal and two vertical lines. The most important elements of your image should be placed along these lines or at their intersections.\n\nLeading lines are another powerful compositional tool. These are lines within your image that guide the viewer\'s eye toward the main subject. They can be roads, rivers, fences, or any other linear element that creates a sense of movement and direction.\n\n## The Magic of Light\n\nLight is the essence of photography. Without light, there is no image. Understanding how to work with different types of light is crucial for creating compelling photographs.\n\n### Natural Light\nNatural light, especially during the golden hour (the hour after sunrise and before sunset), provides the most beautiful and flattering illumination. The soft, warm light creates a magical atmosphere that is perfect for portraits, landscapes, and street photography.\n\n### Artificial Light\nWhen natural light is not available or sufficient, artificial lighting becomes essential. Understanding how to use flash, continuous lighting, and other artificial light sources can help you create stunning images in any situation.\n\n## Timing and Patience\n\nGreat photography often requires patience and perfect timing. Whether you are waiting for the right moment in street photography, the perfect light for landscape photography, or the right expression for portrait photography, timing is everything.\n\n## The Technical Side\n\nWhile creativity and artistic vision are essential, understanding the technical aspects of photography is equally important.\n\n### Aperture\nAperture controls the depth of field in your image. A wide aperture (low f-number) creates a shallow depth of field, perfect for portraits where you want the subject in focus and the background blurred. A narrow aperture (high f-number) creates a deep depth of field, ideal for landscapes where you want everything in focus.\n\n### Shutter Speed\nShutter speed determines how long your camera\'s sensor is exposed to light. Fast shutter speeds freeze motion, while slow shutter speeds create motion blur. Understanding how to use shutter speed creatively can add dynamic elements to your photographs.\n\n### ISO\nISO controls the sensitivity of your camera\'s sensor to light. Higher ISO values allow you to shoot in low light conditions, but they also introduce more noise into your image. Finding the right balance between ISO, aperture, and shutter speed is key to creating well-exposed images.\n\n## The Art of Storytelling\n\nEvery photograph should tell a story. Whether it is a portrait that reveals the personality of the subject, a landscape that captures the beauty of nature, or a street photograph that documents everyday life, your images should evoke emotions and create connections with viewers.\n\n## Post-Processing\n\nPost-processing is an essential part of modern photography. While it is important to get things right in camera, post-processing allows you to enhance your images and bring your creative vision to life. Software like Adobe Lightroom and Photoshop provide powerful tools for editing and enhancing your photographs.\n\n## Finding Your Style\n\nEvery photographer has a unique style that reflects their personality, experiences, and artistic vision. Developing your style takes time and experimentation. Study the work of other photographers, experiment with different techniques, and most importantly, shoot what you love.\n\n## The Journey Never Ends\n\nPhotography is a journey of continuous learning and growth. There is always something new to discover, a new technique to master, or a new perspective to explore. Embrace the learning process, and remember that every photograph you take brings you one step closer to becoming the photographer you want to be.\n\n## Conclusion\n\nPhotography is a beautiful and rewarding art form that allows us to see the world through different eyes. Whether you are a beginner just starting your photographic journey or an experienced photographer looking to refine your skills, remember that the most important thing is to enjoy the process and create images that mean something to you.\n\nKeep shooting, keep learning, and most importantly, keep seeing the beauty in the world around you. Every moment is an opportunity to create something beautiful, and every photograph is a chance to share your unique perspective with the world.\n\nHappy shooting! 📸✨',1,'2025-08-13 23:59:34',1,'uploads/sample_image.svg'),(8,'Hello World !','Hello my new web site',2,'2025-08-14 01:50:33',5,'uploads/689d40e913794_1755136233.jpg'),(9,'New post','New Day !',3,'2025-08-14 02:53:52',NULL,'uploads/689d4fc0e65d5_1755140032.jpg');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'testuser','test@example.com','test123','2025-08-13 23:31:49',NULL,NULL,NULL,NULL),(2,'bavar','bavar@gmail.com','$2y$12$P22F9rC8M6yNGe9Cr47emOJQDFO122wF.rFMcc8Ji.9mCdHQf/mV.','2025-08-13 23:32:52','Erfan','Rezapour','Bavar','uploads/689d498a8a963_1755138442.jpg'),(3,'erfan','erfan@admin.com','$2y$12$d4/uJoDURcVtSVOV9ETMMuh2UWt3sTKdgAs5dO.hIngWg4ICAl.Be','2025-08-14 02:28:32','','','','uploads/689d514eef6f8_1755140430.jpg');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-16 18:39:23
