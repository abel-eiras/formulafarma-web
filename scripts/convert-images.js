import sharp from 'sharp';
import { readdir, stat } from 'fs/promises';
import { join } from 'path';
import { existsSync } from 'fs';

const imagesDir = './public/images';

async function convertToWebP(inputPath, outputPath) {
  try {
    await sharp(inputPath)
      .webp({ quality: 85 })
      .toFile(outputPath);
    console.log(`✅ Convertido: ${inputPath} -> ${outputPath}`);
    return true;
  } catch (error) {
    console.error(`❌ Error convirtiendo ${inputPath}:`, error.message);
    return false;
  }
}

async function processImages() {
  try {
    const files = await readdir(imagesDir);
    const imageFiles = files.filter(file => 
      /\.(png|jpg|jpeg)$/i.test(file) && !file.endsWith('.webp')
    );

    console.log(`\n📸 Encontradas ${imageFiles.length} imágenes para convertir:\n`);

    for (const file of imageFiles) {
      const inputPath = join(imagesDir, file);
      const outputPath = join(imagesDir, file.replace(/\.(png|jpg|jpeg)$/i, '.webp'));
      
      // Solo convertir si no existe ya el WebP
      if (!existsSync(outputPath)) {
        await convertToWebP(inputPath, outputPath);
      } else {
        console.log(`⏭️  Ya existe: ${outputPath}`);
      }
    }

    console.log('\n✨ Conversión completada!\n');
  } catch (error) {
    console.error('Error procesando imágenes:', error);
    process.exit(1);
  }
}

processImages();

